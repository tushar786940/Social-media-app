<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Determine whether the user can view the profile.
     */
    public function view(User $currentUser, User $profileUser): bool
    {
        // Public profiles are always viewable
        if (! $profileUser->is_private) {
            return true;
        }

        // Private profiles can only be viewed by the owner or authenticated users who follow them
        // For now, only the owner can view private profiles
        return $currentUser->id === $profileUser->id;
    }

    /**
     * Determine whether the user can view the profile at the given level.
     * This is used for different privacy levels in the future.
     */
    public function viewPublic(User $currentUser, User $profileUser): bool
    {
        // Public profiles are viewable by everyone
        return ! $profileUser->is_private || $currentUser->id === $profileUser->id;
    }

    /**
     * Determine whether the user can edit the profile.
     */
    public function edit(User $currentUser, User $profileUser): bool
    {
        // Only the owner can edit their profile
        return $currentUser->id === $profileUser->id;
    }

    /**
     * Determine whether the user can update the profile settings.
     */
    public function updateSettings(User $currentUser, User $profileUser): bool
    {
        // Only the owner can update their settings
        return $currentUser->id === $profileUser->id;
    }

    /**
     * Determine whether the user can change the password.
     */
    public function changePassword(User $currentUser, User $profileUser): bool
    {
        // Only the owner can change their password
        return $currentUser->id === $profileUser->id;
    }

    /**
     * Determine whether the user can update privacy settings.
     */
    public function updatePrivacy(User $currentUser, User $profileUser): bool
    {
        // Only the owner can update their privacy settings
        return $currentUser->id === $profileUser->id;
    }

    /**
     * Determine whether the user can upload profile photos.
     */
    public function uploadPhotos(User $currentUser, User $profileUser): bool
    {
        // Only the owner can upload photos
        return $currentUser->id === $profileUser->id;
    }

    /**
     * Determine whether the user can delete profile photos.
     */
    public function deletePhotos(User $currentUser, User $profileUser): bool
    {
        // Only the owner can delete photos
        return $currentUser->id === $profileUser->id;
    }

    /**
     * Determine whether the user can delete the account.
     */
    public function delete(User $currentUser, User $profileUser): bool
    {
        // Only the owner can delete their account
        return $currentUser->id === $profileUser->id;
    }

    /**
     * Determine whether the user can view the posts of another user.
     */
    public function viewPosts(User $currentUser, User $profileUser): bool
    {
        // If the profile is public, anyone can view posts
        if (! $profileUser->is_private) {
            return true;
        }

        // If the profile is private, only the owner can view posts
        return $currentUser->id === $profileUser->id;
    }

    /**
     * Determine whether the user can view private posts.
     */
    public function viewPrivatePosts(User $currentUser, User $profileUser): bool
    {
        // Only the owner can view their own private posts
        return $currentUser->id === $profileUser->id;
    }
}
