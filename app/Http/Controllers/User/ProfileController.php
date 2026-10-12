<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ImageUploadService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * Display the user's public profile.
     */
    public function show(Request $request, User $user)
    {
        // Check if authenticated user can view this user's posts
        if ($request->user()) {
            $this->authorize('viewPosts', $user);
        } else {
            // For guests, only allow viewing if the account is public
            if ($user->is_private) {
                abort(403, 'This account is private.');
            }
        }

        // Get posts with proper visibility
        $query = $user->posts()->with('user')->newest();

        // If the profile is private and we're not the owner, only show public posts
        if ($user->is_private && ($request->user() && $request->user()->id !== $user->id)) {
            $query->public();
        }

        $posts = $query->paginate(10);
        
        // Eager load follow counts
        $user->loadCount(['followers', 'following']);
        
        // Check if current user is following this profile
        $isFollowing = $request->user() ? $user->isFollowedBy($request->user()) : false;
        $hasPendingRequest = $request->user() ? $user->hasPendingRequestFrom($request->user()) : false;

        return view('users.profile', [
            'user' => $user,
            'posts' => $posts,
        ]);
    }

    /**
     * Show the form for editing the user's profile.
     */
    public function edit()
    {
        return view('users.edit-profile', [
            'user' => Auth::user(),
        ]);
    }

    /**
     * Update the user's profile.
     */
    public function update(Request $request, ImageUploadService $uploadService): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => [
                'required',
                'string',
                'max:50',
                Rule::unique('users')->ignore($user->id),
                'alpha_dash',
            ],
            'bio' => ['nullable', 'string', 'max:500'],
            'location' => ['nullable', 'string', 'max:100'],
            'website' => ['nullable', 'url', 'max:255'],
            'birthdate' => ['nullable', 'date', 'before_or_equal:today'],
            'is_private' => ['boolean'],
            'profile_photo' => [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg,gif,webp',
                'max:2048', // 2MB
            ],
            'cover_photo' => [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg,gif,webp',
                'max:2048', // 2MB
            ],
        ]);

        // Handle profile photo upload
        if ($request->hasFile('profile_photo')) {
            $validated['profile_photo_path'] = $uploadService->uploadProfilePhoto(
                $request->file('profile_photo'),
                $user->profile_photo_path
            );
        }

        // Handle cover photo upload
        if ($request->hasFile('cover_photo')) {
            $validated['cover_photo_path'] = $uploadService->uploadCoverPhoto(
                $request->file('cover_photo'),
                $user->cover_photo_path
            );
        }

        // Update user
        $user->update($validated);

        return redirect()->route('users.profile', ['user' => $user->username])
            ->with('success', 'Profile updated successfully!');
    }

    /**
     * Handle profile photo upload via separate endpoint.
     */
    public function uploadProfilePhoto(Request $request, ImageUploadService $uploadService): RedirectResponse
    {
        $user = Auth::user();

        $request->validate([
            'profile_photo' => [
                'required',
                'image',
                'mimes:jpeg,png,jpg,gif,webp',
                'max:2048',
            ],
        ]);

        $uploadService->uploadProfilePhoto(
            $request->file('profile_photo'),
            $user->profile_photo_path
        );

        return back()->with('success', 'Profile photo uploaded successfully!');
    }

    /**
     * Remove the user's profile photo.
     */
    public function removeProfilePhoto(ImageUploadService $uploadService): RedirectResponse
    {
        $user = Auth::user();

        if ($user->profile_photo_path) {
            $uploadService->deleteFile($user->profile_photo_path);
            $user->update(['profile_photo_path' => null]);
        }

        return back()->with('success', 'Profile photo removed successfully!');
    }

    /**
     * Remove the user's cover photo.
     */
    public function removeCoverPhoto(ImageUploadService $uploadService): RedirectResponse
    {
        $user = Auth::user();

        if ($user->cover_photo_path) {
            $uploadService->deleteFile($user->cover_photo_path);
            $user->update(['cover_photo_path' => null]);
        }

        return back()->with('success', 'Cover photo removed successfully!');
    }
}
