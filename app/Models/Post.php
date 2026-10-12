<?php

namespace App\Models;

use App\Models\Comment;
use App\Models\Like;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

#[Fillable(['content', 'image_path', 'visibility'])]
class Post extends Model
{
    /** @use HasFactory<PostFactory> */
    use HasFactory;

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the user that owns the post.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the URL to the post's image.
     */
    protected function imageUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->image_path
                ? Storage::disk('public')->url($this->image_path)
                : null,
        );
    }

    /**
     * Determine if the post has an image.
     */
    public function hasImage(): bool
    {
        return ! is_null($this->image_path);
    }

    /**
     * Get the post's formatted creation date.
     */
    public function formattedDate(): string
    {
        return $this->created_at->format('M j, Y \a\t g:i a');
    }

    /**
     * Get the post's relative creation time.
     */
    public function relativeDate(): string
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Get the post's visibility label.
     */
    public function visibilityLabel(): string
    {
        return match ($this->visibility) {
            'public' => 'Public',
            'private' => 'Private',
            default => 'Public',
        };
    }

    /**
     * Scope a query to only include public posts.
     */
    public function scopePublic($query)
    {
        return $query->where('visibility', 'public');
    }

    /**
     * Scope a query to only include posts from a specific user.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope a query to order by newest first.
     */
    public function scopeNewest($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Get the post's URL.
     */
    public function url(): string
    {
        return route('posts.show', ['post' => $this->id]);
    }

    /**
     * Get the post's edit URL.
     */
    public function editUrl(): string
    {
        return route('posts.edit', ['post' => $this->id]);
    }

    /**
     * Get the likes for the post.
     */
    public function likes(): HasMany
    {
        return $this->hasMany(Like::class);
    }

    /**
     * Get the comments for the post.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Get the top-level comments for the post.
     */
    public function topLevelComments()
    {
        return $this->comments()->topLevel()->with('user', 'replies', 'replies.user')->newest();
    }

    /**
     * Get the like count for the post.
     */
    public function likeCount(): int
    {
        return $this->likes()->count();
    }

    /**
     * Get the comment count for the post.
     */
    public function commentCount(): int
    {
        return $this->comments()->count();
    }

    /**
     * Check if a user has liked the post.
     */
    public function isLikedBy(User $user): bool
    {
        return $this->likes()->where('user_id', $user->id)->exists();
    }

    /**
     * Scope a query to get feed for a user.
     */
    public function scopeForFeed($query, User $user)
    {
        // Get IDs of users the authenticated user can see posts from
        $visibleUserIds = $this->getVisibleUserIdsForFeed($user);

        return $query->whereIn('user_id', $visibleUserIds)
            ->where(function ($q) use ($user) {
                $q->where('visibility', 'public')
                    ->orWhere(function ($q2) use ($user) {
                        $q2->where('visibility', 'private')
                            ->where('user_id', $user->id);
                    });
            })
            ->newest();
    }

    /**
     * Get visible user IDs for feed.
     */
    protected function getVisibleUserIdsForFeed(User $user): array
    {
        $visibleUserIds = [$user->id];

        $following = $user->following()
            ->wherePivot('status', 'accepted')
            ->pluck('users.id')
            ->toArray();

        return array_merge($visibleUserIds, $following);
    }

    /**
     * Scope a query for public discovery feed.
     */
    public function scopePublicDiscovery($query)
    {
        return $query->where('visibility', 'public')
            ->whereHas('user', function ($q) {
                $q->where('is_private', false);
            })
            ->newest();
    }

    /**
     * Get the author's display name.
     */
    public function authorName(): string
    {
        return $this->user->displayName();
    }

    /**
     * Get the author's username.
     */
    public function authorUsername(): string
    {
        return $this->user->username;
    }

    /**
     * Get the author's profile URL.
     */
    public function authorUrl(): string
    {
        return $this->user->profileUrl();
    }

    /**
     * Get the author's profile photo.
     */
    public function authorPhoto(): string
    {
        return $this->user->profile_photo;
    }

