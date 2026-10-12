<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Blade;

#[Fillable(['content', 'parent_id'])]
class Comment extends Model
{
    /** @use HasFactory<CommentFactory> */
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
     * Get the user who made the comment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the post that the comment belongs to.
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * Get the parent comment (for replies).
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    /**
     * Get the replies to this comment.
     */
    public function replies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }

    /**
     * Get all descendants (replies, replies of replies, etc.).
     */
    public function descendants()
    {
        return $this->replies()->with('replies');
    }

    /**
     * Get the escaped content for safe display.
     */
    protected function escapedContent(): Attribute
    {
        return Attribute::make(
            get: fn () => e($this->content),
        );
    }

    /**
     * Get the formatted creation date.
     */
    public function formattedDate(): string
    {
        return $this->created_at->format('M j, Y \a\t g:i a');
    }

    /**
     * Get the relative creation time.
     */
    public function relativeDate(): string
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Check if this comment is a reply.
     */
    public function isReply(): bool
    {
        return $this->parent_id !== null;
    }

    /**
     * Check if this comment is a top-level comment.
     */
    public function isTopLevel(): bool
    {
        return $this->parent_id === null;
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

    /**
     * Get the comment's edit URL.
     */
    public function editUrl(): string
    {
        return route('comments.update', [
            'post' => $this->post_id,
            'comment' => $this->id,
        ]);
    }

    /**
     * Get the comment's delete URL.
     */
    public function deleteUrl(): string
    {
        return route('comments.destroy', [
            'post' => $this->post_id,
            'comment' => $this->id,
        ]);
    }

    /**
     * Get the reply URL.
     */
    public function replyUrl(): string
    {
        return route('comments.store', [
            'post' => $this->post_id,
            'parent' => $this->id,
        ]);
    }

    /**
     * Scope a query for a specific post.
     */
    public function scopeForPost($query, $postId)
    {
        return $query->where('post_id', $postId);
    }

    /**
     * Scope a query for top-level comments only.
     */
    public function scopeTopLevel($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope a query for replies only.
     */
    public function scopeReplies($query)
    {
        return $query->whereNotNull('parent_id');
    }

    /**
     * Scope a query to order by newest first.
     */
    public function scopeNewest($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Scope a query to order by oldest first.
     */
    public function scopeOldest($query)
    {
        return $query->orderBy('created_at', 'asc');
    }

    /**
     * Scope a query with eager loading.
     */
    public function scopeWithRelations($query)
    {
        return $query->with(['user', 'parent', 'replies', 'replies.user']);
    }
}
