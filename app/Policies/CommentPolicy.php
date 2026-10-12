<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;

class CommentPolicy
{
    /**
     * Determine whether the user can create comments on a post.
     */
    public function create(User $user, Post $post): bool
    {
        // User must be authenticated
        if (! $user) {
            return false;
        }

        // Check if user can engage with the post (respects visibility)
        return $post->canEngage($user);
    }

    /**
     * Determine whether the user can create a reply to a comment.
     */
    public function reply(User $user, Comment $comment): bool
    {
        // User must be authenticated
        if (! $user) {
            return false;
        }

        // Check if user can engage with the post that the comment belongs to
        return $comment->post->canEngage($user);
    }

    /**
     * Determine whether the user can update a comment.
     */
    public function update(User $user, Comment $comment): bool
    {
        // Only the comment owner can edit
        return $user && $user->id === $comment->user_id;
    }

    /**
     * Determine whether the user can delete a comment.
     */
    public function delete(User $user, Comment $comment): bool
    {
        // Only the comment owner can delete
        return $user && $user->id === $comment->user_id;
    }

    /**
     * Determine whether the user can view comments for a post.
     */
    public function viewAny(User $user, Post $post): bool
    {
        // Same as canEngage - if they can see the post, they can see comments
        return $post->canEngage($user);
    }

    /**
     * Determine whether the user can view a specific comment.
     */
    public function view(User $user, Comment $comment): bool
    {
        // If user can view the post, they can view the comments
        return $comment->post->canEngage($user);
    }
}
