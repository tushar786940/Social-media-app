<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Post;
use App\Models\Follow;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name', 
    'username',
    'email', 
    'password',
    'bio',
    'location',
    'website',
    'birthdate',
    'is_private',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'birthdate' => 'date',
            'is_private' => 'boolean',
        ];
    }

    /**
     * Get the URL to the user's profile photo.
     */
    protected function profilePhoto(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->profile_photo_path
                ? Storage::disk('public')->url($this->profile_photo_path)
                : $this->defaultProfilePhotoUrl(),
        );
    }

    /**
     * Get the default profile photo URL.
     */
    public function defaultProfilePhotoUrl(): string
    {
        return asset('images/default-avatar.svg');
    }

    /**
     * Get the URL to the user's cover photo.
     */
    protected function coverPhoto(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->cover_photo_path
                ? Storage::disk('public')->url($this->cover_photo_path)
                : null,
        );
    }

    /**
     * Get the user's public profile URL.
     */
    public function profileUrl(): string
    {
        return route('users.profile', ['user' => $this->username]);
    }

    /**
     * Determine if the user has a profile photo.
     */
    public function hasProfilePhoto(): bool
    {
        return ! is_null($this->profile_photo_path);
    }

    /**
     * Determine if the user has a cover photo.
     */
    public function hasCoverPhoto(): bool
    {
        return ! is_null($this->cover_photo_path);
    }

    /**
     * Get the user's display name (name or username fallback).
     */
    public function displayName(): string
    {
        return $this->name ?: $this->username;
    }

    /**
     * Get the user's formatted birthdate.
     */
    public function formattedBirthdate(): ?string
    {
        if (! $this->birthdate) {
            return null;
        }

        return $this->birthdate->format('F j, Y');
    }

    /**
     * Get the user's age.
     */
    public function age(): ?int
    {
        if (! $this->birthdate) {
            return null;
        }

        return $this->birthdate->age;
    }

    /**
     * Get the posts for the user.
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    /**
     * Get the public posts for the user.
     */
    public function publicPosts()
    {
        return $this->posts()->public()->newest();
    }

    /**
     * Get the post count for the user.
     */
    public function postCount(): int
    {
        return $this->posts()->count();
    }

    /**
     * Get the users that this user is following.
     */
    public function following(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'follows',
            'follower_id',
            'following_id'
        )->withPivot('status', 'created_at')
          ->withTimestamps();
    }

    /**
     * Get the users that follow this user.
     */
    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'follows',
            'following_id',
            'follower_id'
        )->withPivot('status', 'created_at')
          ->withTimestamps();
    }

    /**
     * Get the accepted follows (users this user is following).
     */
    public function followingAccepted()
    {
        return $this->following()->wherePivot('status', 'accepted');
    }

    /**
     * Get the accepted followers.
     */
    public function followersAccepted()
    {
        return $this->followers()->wherePivot('status', 'accepted');
    }

    /**
     * Get the pending follow requests (users trying to follow this user).
     */
    public function followRequests()
    {
        return $this->followers()->wherePivot('status', 'pending');
    }

    /**
     * Get the count of users this user is following.
     */
    public function followingCount(): int
    {
        return $this->following()->wherePivot('status', 'accepted')->count();
    }

    /**
     * Get the count of followers for this user.
     */
    public function followerCount(): int
    {
        return $this->followers()->wherePivot('status', 'accepted')->count();
    }

    /**
     * Get the count of pending follow requests.
     */
    public function pendingFollowRequestsCount(): int
    {
        return $this->followRequests()->count();
    }

    /**
     * Check if this user is following another user.
     */
    public function isFollowing(User $user): bool
    {
        if ($this->id === $user->id) {
            return false;
        }

        return $this->following()
            ->where('following_id', $user->id)
            ->wherePivot('status', 'accepted')
            ->exists();
    }

    /**
     * Check if this user has a pending follow request for another user.
     */
    public function hasPendingFollowRequestFor(User $user): bool
    {
        if ($this->id === $user->id) {
            return false;
        }

        return $this->following()
            ->where('following_id', $user->id)
            ->wherePivot('status', 'pending')
            ->exists();
    }

    /**
     * Check if another user is following this user.
     */
    public function isFollowedBy(User $user): bool
    {
        if ($this->id === $user->id) {
            return false;
        }

        return $this->followers()
            ->where('follower_id', $user->id)
            ->wherePivot('status', 'accepted')
            ->exists();
    }

    /**
     * Check if another user has a pending follow request for this user.
     */
    public function hasPendingRequestFrom(User $user): bool
    {
        if ($this->id === $user->id) {
            return false;
        }

        return $this->followers()
            ->where('follower_id', $user->id)
            ->wherePivot('status', 'pending')
            ->exists();
    }

    /**
     * Get the follow relationship with another user.
     */
    public function followRelationship(User $user)
    {
        if ($this->id === $user->id) {
            return null;
        }

        return Follow::where('follower_id', $this->id)
            ->where('following_id', $user->id)
            ->first();
    }

    /**
     * Check if user can view another user's content based on follow status.
     */
    public function canView(User $profileUser): bool
    {
        // If profile is public, anyone can view
        if (! $profileUser->is_private) {
            return true;
        }

        // If profile is private, only owner and accepted followers can view
        return $this->id === $profileUser->id || $profileUser->isFollowedBy($this);
    }
}
