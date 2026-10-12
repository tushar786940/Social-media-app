<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Database\DatabaseManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    /**
     * Store a new comment on a post.
     */
    public function store(Request $request, Post $post, DatabaseManager $db): JsonResponse|RedirectResponse
    {
        $user = $request->user();

        // Check if user can engage with the post
        if (! $post->canEngage($user)) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            return back()->with('error', 'You cannot comment on this post.');
        }

        $validated = $request->validate([
            'content' => ['required', 'string', 'min:1', 'max:2000'],
            'parent_id' => ['nullable', 'integer', 'exists:comments,id'],
        ]);

        // If parent_id is provided, check that it belongs to the same post
        if ($request->parent_id) {
            $parent = Comment::findOrFail($request->parent_id);
            if ($parent->post_id !== $post->id) {
                if ($request->expectsJson()) {
                    return response()->json(['error' => 'Invalid parent comment'], 422);
                }
                return back()->with('error', 'Invalid parent comment.');
            }
        }

        return $db->transaction(function () use ($user, $post, $validated, $request) {
            $comment = $post->comments()->create([
                'user_id' => $user->id,
                'content' => $validated['content'],
                'parent_id' => $validated['parent_id'] ?? null,
            ]);

            // Load the comment with user for the response
            $comment->load('user');

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'comment' => [
                        'id' => $comment->id,
                        'content' => $comment->escapedContent,
                        'user' => [
                            'name' => $comment->user->displayName(),
                            'username' => $comment->user->username,
                            'photo' => $comment->user->profile_photo,
                        ],
                        'created_at' => $comment->relativeDate(),
                        'isReply' => $comment->isReply(),
                    ],
                    'commentCount' => $post->fresh()->commentCount(),
                ]);
            }

            return back()->with('success', 'Comment added successfully!');
        });
    }

    /**
     * Update a comment.
     */
    public function update(Request $request, Post $post, Comment $comment, DatabaseManager $db): JsonResponse|RedirectResponse
    {
        $user = $request->user();

        // Check authorization
        $this->authorize('update', $comment);

        $validated = $request->validate([
            'content' => ['required', 'string', 'min:1', 'max:2000'],
        ]);

        return $db->transaction(function () use ($comment, $validated, $request) {
            $comment->update(['content' => $validated['content']]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'comment' => [
                        'id' => $comment->id,
                        'content' => $comment->escapedContent,
                        'updated_at' => $comment->relativeDate(),
                    ],
                ]);
            }

            return back()->with('success', 'Comment updated successfully!');
        });
    }

    /**
     * Delete a comment.
     */
    public function destroy(Request $request, Post $post, Comment $comment, DatabaseManager $db): JsonResponse|RedirectResponse
    {
        $user = $request->user();

        // Check authorization
        $this->authorize('delete', $comment);

        return $db->transaction(function () use ($comment, $post, $request) {
            $comment->delete();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'commentCount' => $post->fresh()->commentCount(),
                ]);
            }

            return back()->with('success', 'Comment deleted successfully!');
        });
    }

    /**
     * Get comments for a post.
     */
    public function index(Request $request, Post $post)
    {
        // Check if user can view the post
        if (! $post->canEngage($request->user())) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            abort(403, 'You cannot view comments on this post.');
        }

        $comments = $post->topLevelComments()
            ->with(['user', 'replies' => function ($query) {
                $query->with('user')->newest();
            }])
            ->newest()
            ->paginate(10);

        if ($request->expectsJson()) {
            return response()->json([
                'comments' => $comments->map(function ($comment) {
                    return [
                        'id' => $comment->id,
                        'content' => $comment->escapedContent,
                        'user' => [
                            'id' => $comment->user->id,
                            'name' => $comment->user->displayName(),
                            'username' => $comment->user->username,
                            'photo' => $comment->user->profile_photo,
                        ],
                        'created_at' => $comment->relativeDate(),
                        'isReply' => false,
                        'canEdit' => Auth::id() === $comment->user_id,
                        'replies' => $comment->replies->map(function ($reply) {
                            return [
                                'id' => $reply->id,
                                'content' => $reply->escapedContent,
                                'user' => [
                                    'id' => $reply->user->id,
                                    'name' => $reply->user->displayName(),
                                    'username' => $reply->user->username,
                                    'photo' => $reply->user->profile_photo,
                                ],
                                'created_at' => $reply->relativeDate(),
                                'isReply' => true,
                                'canEdit' => Auth::id() === $reply->user_id,
                            ];
                        }),
                    ];
                }),
                'commentCount' => $post->commentCount(),
            ]);
        }

        return view('posts.comments', [
            'post' => $post,
            'comments' => $comments,
        ]);
    }
}
