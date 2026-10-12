<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Database\DatabaseManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LikeController extends Controller
{
    /**
     * Like a post.
     */
    public function store(Request $request, Post $post, DatabaseManager $db): JsonResponse|RedirectResponse
    {
        $user = $request->user();

        // Check if user can engage with the post
        if (! $post->canEngage($user)) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            return back()->with('error', 'You cannot like this post.');
        }

        // Check if already liked
        if ($post->isLikedBy($user)) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Already liked'], 422);
            }
            return back()->with('error', 'You have already liked this post.');
        }

        return $db->transaction(function () use ($user, $post, $request) {
            $user->likes()->create([
                'post_id' => $post->id,
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'likeCount' => $post->fresh()->likeCount(),
                    'isLiked' => true,
                ]);
            }

            return back()->with('success', 'Post liked successfully!');
        });
    }

    /**
     * Unlike a post.
     */
    public function destroy(Request $request, Post $post, DatabaseManager $db): JsonResponse|RedirectResponse
    {
        $user = $request->user();

        // Check if user has liked the post
        if (! $post->isLikedBy($user)) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Not liked'], 422);
            }
            return back()->with('error', 'You have not liked this post.');
        }

        return $db->transaction(function () use ($user, $post, $request) {
            $user->likes()->where('post_id', $post->id)->delete();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'likeCount' => $post->fresh()->likeCount(),
                    'isLiked' => false,
                ]);
            }

            return back()->with('success', 'Post unliked successfully!');
        });
    }

    /**
     * Toggle like on a post (for JavaScript convenience).
     */
    public function toggle(Request $request, Post $post, DatabaseManager $db): JsonResponse
    {
        $user = $request->user();

        if (! $post->canEngage($user)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return $db->transaction(function () use ($user, $post) {
            $isLiked = $post->isLikedBy($user);
            
            if ($isLiked) {
                $user->likes()->where('post_id', $post->id)->delete();
                $newLikeCount = $post->fresh()->likeCount();
                return response()->json([
                    'success' => true,
                    'action' => 'unliked',
                    'likeCount' => $newLikeCount,
                    'isLiked' => false,
                ]);
            } else {
                $user->likes()->create(['post_id' => $post->id]);
                $newLikeCount = $post->fresh()->likeCount();
                return response()->json([
                    'success' => true,
                    'action' => 'liked',
                    'likeCount' => $newLikeCount,
                    'isLiked' => true,
                ]);
            }
        });
    }
}
