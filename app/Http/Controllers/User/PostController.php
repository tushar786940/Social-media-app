<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\User;
use App\Services\ImageUploadService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class PostController extends Controller
{
    /**
     * Display a listing of posts.
     */
    public function index(Request $request)
    {
        // Get posts from users that the authenticated user can see
        $query = Post::with('user')->newest();

        // If authenticated, show posts from followed users or public posts
        // For now, just show public posts and own posts
        if ($request->user()) {
            $query->where(function ($q) use ($request) {
                $q->where('visibility', 'public')
                    ->orWhere('user_id', $request->user()->id);
            });
        } else {
            // Only show public posts for guests
            $query->public();
        }

        $posts = $query->paginate(10);

        return view('posts.index', [
            'posts' => $posts,
        ]);
    }

    /**
     * Display a listing of posts for a specific user.
     */
    public function userPosts(Request $request, $username)
    {
        $profileUser = \App\Models\User::where('username', $username)->firstOrFail();

        // Check if authenticated user can view this user's posts
        if ($request->user()) {
            $this->authorize('viewPosts', $profileUser);
        } else {
            // For guests, only allow viewing if the account is public
            if ($profileUser->is_private) {
                abort(403, 'This account is private.');
            }
        }

        // Get posts with proper visibility
        $query = Post::with('user')
            ->forUser($profileUser->id)
            ->newest();

        // If the profile is private and we're not the owner, only show public posts
        if ($profileUser->is_private && ($request->user() && $request->user()->id !== $profileUser->id)) {
            $query->public();
        }

        $posts = $query->paginate(10);

        return view('users.profile', [
            'user' => $profileUser,
            'posts' => $posts,
        ]);
    }

    /**
     * Show the form for creating a new post.
     */
    public function create(Request $request)
    {
        $this->authorize('create', Post::class);

        $defaultVisibility = $request->user()->is_private ? 'private' : 'public';

        return view('posts.create', [
            'defaultVisibility' => $defaultVisibility,
        ]);
    }

    /**
     * Store a newly created post in storage.
     */
    public function store(Request $request, ImageUploadService $uploadService): RedirectResponse
    {
        $this->authorize('create', Post::class);

        $validated = $request->validate([
            'content' => ['required', 'string', 'max:5000'],
            'image' => [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg,gif,webp',
                'max:2048', // 2MB
            ],
            'visibility' => [
                'required',
                'string',
                Rule::in(['public', 'private']),
            ],
        ]);

        $post = $request->user()->posts()->create([
            'content' => $validated['content'],
            'visibility' => $validated['visibility'],
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            $imagePath = $uploadService->uploadPostImage(
                $request->file('image')
            );
            $post->update(['image_path' => $imagePath]);
        }

        return redirect()->route('posts.show', ['post' => $post->id])
            ->with('success', 'Post created successfully!');
    }

    /**
     * Display the specified post.
     */
    public function show(Post $post)
    {
        $this->authorize('view', $post);

        // Eager load relationships
        $post->load([
            'user',
            'likes',
            'comments' => function ($query) {
                $query->with(['user', 'replies' => function ($q) {
                    $q->with('user')->newest();
                }])->newest();
            },
        ]);

        return view('posts.show', [
            'post' => $post,
        ]);
    }

    /**
     * Show the form for editing the specified post.
     */
    public function edit(Post $post)
    {
        $this->authorize('update', $post);

        return view('posts.edit', [
            'post' => $post,
        ]);
    }

    /**
     * Update the specified post in storage.
     */
    public function update(Request $request, Post $post, ImageUploadService $uploadService): RedirectResponse
    {
        $this->authorize('update', $post);

        $validated = $request->validate([
            'content' => ['required', 'string', 'max:5000'],
            'image' => [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg,gif,webp',
                'max:2048',
            ],
            'visibility' => [
                'required',
                'string',
                Rule::in(['public', 'private']),
            ],
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            $imagePath = $uploadService->uploadPostImage(
                $request->file('image'),
                $post->image_path
            );
            $validated['image_path'] = $imagePath;
        }

        $post->update($validated);

        return redirect()->route('posts.show', ['post' => $post->id])
            ->with('success', 'Post updated successfully!');
    }

    /**
     * Remove the specified post from storage.
     */
    public function destroy(Post $post, ImageUploadService $uploadService): RedirectResponse
    {
        $this->authorize('delete', $post);

        // Delete the image if it exists
        if ($post->image_path) {
            $uploadService->deleteFile($post->image_path);
        }

        $post->delete();

        return redirect()->route('users.profile', ['user' => Auth::user()->username])
            ->with('success', 'Post deleted successfully!');
    }

    /**
     * Remove the image from a post.
     */
    public function removeImage(Post $post, ImageUploadService $uploadService): RedirectResponse
    {
        $this->authorize('deleteImage', $post);

        if ($post->image_path) {
            $uploadService->deleteFile($post->image_path);
            $post->update(['image_path' => null]);
        }

        return back()->with('success', 'Post image removed successfully!');
    }
}
