<?php

namespace App\Policies;

use App\Models\Like;
use App\Models\Post;
use App\Models\User;

class LikePolicy
{
    /**
     * Determine whether the user can like a post.
     */
    public function create(User $user, Post $post): bool
    {
        // User must be authenticated
        if (! $user) {
            return false;
        }

        // Cannot like own post? Actually, users can like their own posts
        // But we need to check if they can view the post
        
        // Check if user can engage with the post (respects visibility)
        return $post->canEngage($user);
    }

    /**
     * Determine whether the user can unlike a post.
     */
    public function delete(User $user, Post $post): bool
    {
        // User must be authenticated
        if (! $user) {
            return false;
        }

        // Can only unlike if they have liked it
        return $post->isLikedBy($user);
    }

    /**
     * Determine whether the user can view likes for a post.
     */
    public function viewAny(User $user, Post $post): bool
    {
        // Same as canEngage - if they can see the post, they can see likes
        return $post->canEngage($user);
    }

    /**
     * Determine whether the user can view a specific like.
     */
    public function view(User $user, Like $like): bool
    {
        // If user can view the post, they can view the likes
        return $like->post->canEngage($user);
    }
}
