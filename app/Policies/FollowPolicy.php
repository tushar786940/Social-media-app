<?php

namespace App\Policies;

use App\Models\Follow;
use App\Models\User;

class FollowPolicy
{
    /**
     * Determine whether the user can follow another user.
     */
    public function follow(User $user, User $profileUser): bool
    {
        // Cannot follow oneself
        if ($user->id === $profileUser->id) {
            return false;
        }

        // Can always attempt to follow (will create pending for private accounts)
        return true;
    }

    /**
     * Determine whether the user can unfollow another user.
     */
    public function unfollow(User $user, User $profileUser): bool
    {
        // Cannot unfollow oneself
        if ($user->id === $profileUser->id) {
            return false;
        }

        // Can only unfollow if currently following
        return $user->isFollowing($profileUser);
    }

    /**
     * Determine whether the user can accept follow requests.
     */
    public function accept(User $user, User $follower): bool
    {
        // Only the profile owner (user) can accept requests from followers
        // The follower is the one who wants to follow
        // The user is the one being followed
        
        // Check if there's a pending request from follower to user
        $hasPendingRequest = Follow::where('follower_id', $follower->id)
            ->where('following_id', $user->id)
            ->where('status', 'pending')
            ->exists();

        return $hasPendingRequest;
    }

    /**
     * Determine whether the user can reject follow requests.
     */
    public function reject(User $user, User $follower): bool
    {
        // Same logic as accept
        $hasPendingRequest = Follow::where('follower_id', $follower->id)
            ->where('following_id', $user->id)
            ->where('status', 'pending')
            ->exists();

        return $hasPendingRequest;
    }

    /**
     * Determine whether the user can view followers list.
     */
    public function viewFollowers(User $user, User $profileUser): bool
    {
        // Public accounts: anyone can view followers
        if (! $profileUser->is_private) {
            return true;
        }

        // Private accounts: only owner and accepted followers can view
        return $user->id === $profileUser->id || $profileUser->isFollowedBy($user);
    }

    /**
     * Determine whether the user can view following list.
     */
    public function viewFollowing(User $user, User $profileUser): bool
    {
        // Public accounts: anyone can view who they follow
        if (! $profileUser->is_private) {
            return true;
        }

        // Private accounts: only owner can view who they follow
        return $user->id === $profileUser->id;
    }

    /**
     * Determine whether a follow action is allowed.
     */
    public function create(User $user, User $profileUser): bool
    {
        return $this->follow($user, $profileUser);
    }

    /**
     * Determine whether an unfollow action is allowed.
     */
    public function delete(User $user, User $profileUser): bool
    {
        return $this->unfollow($user, $profileUser);
    }
}
