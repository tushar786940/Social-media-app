<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;

class PostPolicy
{
    /**
     * Determine whether the user can view the post.
     */
    public function view(User $user, Post $post): bool
    {
        // Public posts are always viewable
        if ($post->visibility === 'public') {
            return true;
        }

        // Private posts can only be viewed by the owner
        // or if the user's account is not private
        if ($post->user->is_private) {
            return $user->id === $post->user_id;
        }

        // If the author's account is public, private posts are still only for owner
        return $user->id === $post->user_id;
    }

    /**
     * Determine whether the user can view any posts.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create posts.
     */
    public function create(User $user): bool
    {
        return $user->exists;
    }

    /**
     * Determine whether the user can update the post.
     */
    public function update(User $user, Post $post): bool
    {
        // Only the owner can edit their posts
        return $user->id === $post->user_id;
    }

    /**
     * Determine whether the user can delete the post.
     */
    public function delete(User $user, Post $post): bool
    {
        // Only the owner can delete their posts
        return $user->id === $post->user_id;
    }

    /**
     * Determine whether the user can restore the post.
     */
    public function restore(User $user, Post $post): bool
    {
        // Only the owner can restore their posts
        return $user->id === $post->user_id;
    }

    /**
     * Determine whether the user can permanently delete the post.
     */
    public function forceDelete(User $user, Post $post): bool
    {
        // Only the owner can force delete their posts
        return $user->id === $post->user_id;
    }

    /**
     * Determine whether the user can upload images for the post.
     */
    public function uploadImage(User $user, Post $post): bool
    {
        // Only the owner can upload images
        return $user->id === $post->user_id;
    }

    /**
     * Determine whether the user can delete images from the post.
     */
    public function deleteImage(User $user, Post $post): bool
    {
        // Only the owner can delete images
        return $user->id === $post->user_id;
    }

    /**
     * Determine the default visibility for new posts based on user's account privacy.
     */
    public function defaultVisibility(User $user): string
    {
        // If user's account is private, default to private posts
        return $user->is_private ? 'private' : 'public';
    }
}
