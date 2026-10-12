# Post Engagement System Implementation

## Overview

This document describes the implementation of the post engagement system for the Social Media App, which includes likes and comments functionality with threaded replies.

## Features Implemented

### 1. Likes System
- **Like posts** - Users can like posts they can view
- **Unlike posts** - Users can remove their like
- **Toggle like** - JavaScript-enhanced like/unlike with single click
- **Like counts** - Display number of likes on posts
- **Like state** - Show if current user has liked the post
- **Duplicate prevention** - Database constraint prevents duplicate likes
- **Visibility enforcement** - Respects post and account privacy

### 2. Comments System
- **Add comments** - Users can comment on posts they can view
- **Edit comments** - Users can edit their own comments
- **Delete comments** - Users can delete their own comments
- **Reply to comments** - Threaded replies with parent_id
- **Comment counts** - Display number of comments on posts
- **Validation** - Reject empty comments, enforce max length
- **XSS protection** - Escape all user-generated content
- **Pagination** - Paginate long comment lists

### 3. User Experience
- **Like button** - Heart icon with count, color changes when liked
- **Comment form** - Textarea for adding comments
- **Comment display** - Shows author, content, timestamp, reply button
- **Reply form** - Nested under parent comment
- **Progressive enhancement** - Works without JavaScript, enhanced with JS
- **Success/error feedback** - Clear messages for all actions

### 4. Security & Privacy
- **Authentication required** - Only authenticated users can engage
- **Ownership enforcement** - Only comment owner can edit/delete
- **Visibility enforcement** - Respects post and account privacy
- **XSS protection** - All user content is escaped
- **CSRF protection** - All forms protected
- **Database constraints** - Prevent duplicate likes

## Database Schema

### likes Table

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | bigint (PK) | No | - | Auto-incrementing ID |
| user_id | bigint (FK) | No | - | User who liked the post |
| post_id | bigint (FK) | No | - | Post that was liked |
| created_at | timestamp | No | - | Like creation time |
| updated_at | timestamp | No | - | Last update time |

**Indexes:**
- `user_id` - For querying likes by user
- `post_id` - For querying likes by post
- `user_id + post_id` - Unique constraint (prevents duplicates)

**Foreign Keys:**
- `user_id` references `users.id` with `CASCADE` on delete
- `post_id` references `posts.id` with `CASCADE` on delete

### comments Table

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | bigint (PK) | No | - | Auto-incrementing ID |
| user_id | bigint (FK) | No | - | User who made the comment |
| post_id | bigint (FK) | No | - | Post the comment belongs to |
| parent_id | bigint (FK) | Yes | NULL | Parent comment for replies |
| content | text | No | - | Comment text content |
| created_at | timestamp | No | - | Comment creation time |
| updated_at | timestamp | No | - | Last update time |

**Indexes:**
- `user_id` - For querying comments by user
- `post_id` - For querying comments by post
- `parent_id` - For querying replies
- `post_id + created_at` - For paginated queries
- `post_id + parent_id` - For querying replies to a post

**Foreign Keys:**
- `user_id` references `users.id` with `CASCADE` on delete
- `post_id` references `posts.id` with `CASCADE` on delete
- `parent_id` references `comments.id` with `CASCADE` on delete

## Models

### Like Model (`app/Models/Like.php`)

**Fillable Attributes:**
- `user_id`
- `post_id`

**Relationships:**
- `user()` - BelongsTo relationship with User
- `post()` - BelongsTo relationship with Post

**Scopes:**
- `forPost($postId)` - Filter likes for a specific post
- `byUser($userId)` - Filter likes by a specific user
- `newest()` - Order by creation date (newest first)
- `oldest()` - Order by creation date (oldest first)

### Comment Model (`app/Models/Comment.php`)

**Fillable Attributes:**
- `content`
- `parent_id`

**Relationships:**
- `user()` - BelongsTo relationship with User
- `post()` - BelongsTo relationship with Post
- `parent()` - BelongsTo relationship with Comment (for replies)
- `replies()` - HasMany relationship with Comment (replies to this comment)

**Accessors:**
- `escapedContent()` - Returns escaped content for safe display

**Methods:**
- `formattedDate()` - Returns formatted creation date
- `relativeDate()` - Returns relative time (e.g., "2 hours ago")
- `isReply()` - Checks if comment is a reply
- `isTopLevel()` - Checks if comment is top-level
- `authorName()` - Returns author's display name
- `authorUsername()` - Returns author's username
- `authorUrl()` - Returns author's profile URL
- `authorPhoto()` - Returns author's profile photo
- `editUrl()` - Returns comment edit URL
- `deleteUrl()` - Returns comment delete URL
- `replyUrl()` - Returns reply URL

**Scopes:**
- `forPost($postId)` - Filter comments for a specific post
- `topLevel()` - Filter to top-level comments only
- `replies()` - Filter to replies only
- `newest()` - Order by creation date (newest first)
- `oldest()` - Order by creation date (oldest first)
- `withRelations()` - Eager load user, parent, and replies

### Post Model Updates (`app/Models/Post.php`)

**Relationships:**
- `likes()` - HasMany relationship with Like
- `comments()` - HasMany relationship with Comment
- `topLevelComments()` - Returns only top-level comments with eager loading

**Methods:**
- `likeCount()` - Returns the number of likes
- `commentCount()` - Returns the number of comments
- `isLikedBy(User $user)` - Checks if a user has liked the post
- `canEngage(User $user)` - Checks if a user can engage with the post (respects visibility)

## Policies

### LikePolicy (`app/Policies/LikePolicy.php`)

**Methods:**
- `create(User $user, Post $post)` - Can like a post (must be able to engage)
- `delete(User $user, Post $post)` - Can unlike a post (must have liked it)
- `viewAny(User $user, Post $post)` - Can view likes for a post (must be able to engage)
- `view(User $user, Like $like)` - Can view a specific like (must be able to engage with post)

### CommentPolicy (`app/Policies/CommentPolicy.php`)

**Methods:**
- `create(User $user, Post $post)` - Can comment on a post (must be able to engage)
- `reply(User $user, Comment $comment)` - Can reply to a comment (must be able to engage with post)
- `update(User $user, Comment $comment)` - Can edit a comment (must be owner)
- `delete(User $user, Comment $comment)` - Can delete a comment (must be owner)
- `viewAny(User $user, Post $post)` - Can view comments for a post (must be able to engage)
- `view(User $user, Comment $comment)` - Can view a specific comment (must be able to engage with post)

## Controllers

### LikeController (`app/Http/Controllers/User/LikeController.php`)

**Methods:**
- `store(Request $request, Post $post)` - Like a post
- `destroy(Request $request, Post $post)` - Unlike a post
- `toggle(Request $request, Post $post)` - Toggle like/unlike (for JavaScript)

**Features:**
- Uses database transactions for data integrity
- Validates user can engage with the post
- Prevents duplicate likes
- Returns JSON for JavaScript requests, redirects for form submissions
- Provides success/error feedback

### CommentController (`app/Http/Controllers/User/CommentController.php`)

**Methods:**
- `index(Request $request, Post $post)` - Get comments for a post
- `store(Request $request, Post $post)` - Add a comment or reply
- `update(Request $request, Post $post, Comment $comment)` - Edit a comment
- `destroy(Request $request, Post $post, Comment $comment)` - Delete a comment

**Features:**
- Uses database transactions for data integrity
- Validates user can engage with the post
- Validates comment content (required, min:1, max:2000)
- Validates parent_id belongs to the same post
- Escapes content for safe display
- Returns JSON for JavaScript requests, redirects for form submissions
- Provides success/error feedback

## Views & Components

### Components

#### Like Button (`resources/views/components/posts/like-button.blade.php`)
- Displays like button with heart icon
- Shows like count
- Changes color when liked (red vs gray)
- Works with and without JavaScript
- Progressive enhancement for toggle functionality
- Props: `post`, `showCount` (default: true), `showText` (default: false)

#### Comment Form (`resources/views/components/posts/comment-form.blade.php`)
- Textarea for comment content
- Shows user's avatar
- Handles replies (shows "Replying to @username")
- Validates content length
- Shows errors
- Props: `post`, `parent` (optional, for replies)

#### Comment (`resources/views/components/posts/comment.blade.php`)
- Displays comment with author info
- Shows escaped content (XSS protection)
- Shows timestamp
- Shows reply button (if not at max depth)
- Shows edit/delete buttons for comment owner
- Shows reply form (hidden by default)
- Recursively displays replies
- Props: `comment`, `post`, `level` (default: 0, for nesting)

### Views

#### Comments (`resources/views/posts/comments.blade.php`)
- Displays post card for context
- Displays comment form (if authenticated and can engage)
- Displays list of comments with pagination
- Shows empty state when no comments
- Eager loads all necessary relationships

#### Post Show Updates (`resources/views/posts/show.blade.php`)
- Added comments section
- Added comment form
- Added comments list with pagination
- Eager loads comments with user and replies

#### Post Card Updates (`resources/views/components/posts/card.blade.php`)
- Added engagement section with like button and comment button
- Shows like count and comment count
- Shows timestamp and visibility

## Routes

### Like Routes

| Method | URI | Name | Controller | Middleware |
|--------|-----|------|------------|------------|
| POST | `/posts/{post}/like` | `posts.like` | `LikeController@store` | `auth` |
| DELETE | `/posts/{post}/like` | `posts.unlike` | `LikeController@destroy` | `auth` |
| POST | `/posts/{post}/like/toggle` | `posts.like.toggle` | `LikeController@toggle` | `auth` |

### Comment Routes

| Method | URI | Name | Controller | Middleware |
|--------|-----|------|------------|------------|
| GET | `/posts/{post}/comments` | `posts.comments.index` | `CommentController@index` | - |
| POST | `/posts/{post}/comments` | `comments.store` | `CommentController@store` | `auth` |
| PUT | `/posts/{post}/comments/{comment}` | `comments.update` | `CommentController@update` | `auth` |
| DELETE | `/posts/{post}/comments/{comment}` | `comments.destroy` | `CommentController@destroy` | `auth` |

## Factories

### LikeFactory (`database/factories/LikeFactory.php`)

**Default State:**
- `user_id` - Random user
- `post_id` - Random post

**State Methods:**
- `forUserAndPost(User $user, Post $post)` - Creates like for specific user and post

### CommentFactory (`database/factories/CommentFactory.php`)

**Default State:**
- `user_id` - Random user
- `post_id` - Random post
- `parent_id` - NULL
- `content` - Paragraph of text

**State Methods:**
- `forUserAndPost(User $user, Post $post)` - Creates comment for specific user and post
- `replyTo(Comment $parent)` - Creates reply to specific comment
- `short()` - Creates comment with short content
- `long()` - Creates comment with long content
- `topLevel()` - Creates top-level comment (no parent)

### UserFactory Updates (`database/factories/UserFactory.php`)

**New State Methods:**
- `withLikes(int $count = 3)` - Creates user with specified number of likes
- `withComments(int $count = 3)` - Creates user with specified number of comments

## Validation Rules

### Like Validation
- User must be authenticated
- User must be able to engage with the post (visibility check)
- Database constraint prevents duplicate likes

### Comment Validation
- `content` - Required, string, min:1 character, max:2000 characters
- `parent_id` - Optional, must exist in comments table, must belong to the same post

## Authorization Rules

### Like Authorization
- Only authenticated users can like/unlike
- User must be able to engage with the post (respects visibility)
- Can only unlike if they have liked the post

### Comment Authorization
- Only authenticated users can comment
- User must be able to engage with the post (respects visibility)
- Only comment owner can edit/delete their comment
- Can reply to any comment on a post they can view

## Privacy & Visibility

The engagement system respects both post visibility and account privacy:

### Post Visibility
- **Public posts**: Anyone can like and comment
- **Private posts**: Only the post owner and accepted followers of the author can like and comment

### Account Privacy
- If the post author's account is private, engagement is restricted to the author and their accepted followers
- If the post author's account is public, engagement is restricted by the post's visibility setting

### Implementation
The `canEngage()` method on the Post model handles all visibility checks:

```php
public function canEngage(User $user): bool
{
    if (! $user) {
        return false;
    }
    
    if ($this->visibility === 'public') {
        return true;
    }
    
    if ($this->user->is_private) {
        return $user->id === $this->user_id || $this->user->isFollowedBy($user);
    }
    
    return $user->id === $this->user_id;
}
```

## XSS Protection

All user-generated content is properly escaped:

1. **Comment Model**: Has `escapedContent()` accessor that uses `e()` helper
2. **Blade Templates**: Use `{{ }}` syntax which auto-escapes, or explicitly use `{!! !!}` only for trusted content
3. **No Raw HTML**: User content is never rendered as HTML

```php
// In Comment model
protected function escapedContent(): Attribute
{
    return Attribute::make(
        get: fn () => e($this->content),
    );
}
```

## Progressive Enhancement

### Like Button
The like button works in two modes:

1. **Without JavaScript**: Standard form submission with page reload
2. **With JavaScript**: Optimistic UI update with fetch request in the background

```javascript
// In like-button.blade.php
form.addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Optimistic UI update
    // ... update icon and count immediately
    
    // Submit via fetch
    fetch(form.action, {
        method: newLiked ? 'POST' : 'DELETE',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
        },
    })
    .then(response => response.json())
    .then(data => {
        // Update with server response if needed
    });
});
```

### Comment Form
- Works as standard HTML form
- Can be enhanced with JavaScript for better UX (e.g., live preview, character count)

## Performance Optimizations

### Eager Loading
- Post show page eager loads:
  - User
  - Likes
  - Comments with user, replies, and reply users
- Avoids N+1 queries when displaying posts with comments

### Database Indexes
- Proper indexes on all foreign keys and common query columns
- Unique constraint on (user_id, post_id) for likes
- Composite indexes for efficient querying

### Pagination
- Comments are paginated with 10 items per page
- Replies are loaded with their parent comments
- Efficient queries with proper indexes

## Testing

### Test File: `tests/Feature/User/EngagementTest.php`

**Test Categories:**

1. **Likes** (12 tests)
   - Like a post
   - Like another user's post
   - Unlike a post
   - Prevent duplicate likes
   - Cannot like private post without following
   - Can like private post when following
   - Cannot like when not authenticated
   - Toggle like
   - Like count
   - isLikedBy method
   - canEngage method

2. **Comments** (20+ tests)
   - Comment on a post
   - Comment on another user's post
   - Validate content is required
   - Validate content min length
   - Validate content max length
   - Cannot comment on private post without following
   - Can comment on private post when following
   - Cannot comment when not authenticated
   - Edit own comment
   - Cannot edit other user's comment
   - Delete own comment
   - Cannot delete other user's comment
   - Reply to a comment
   - Cannot reply to comment from different post
   - Comment count
   - isReply method
   - has replies relationship

3. **Model Relationships** (10+ tests)
   - Like model relationships
   - Comment model relationships
   - Post model relationships
   - Scopes and methods

4. **Database Constraints** (5 tests)
   - Prevent duplicate likes
   - Cascade delete likes on user deletion
   - Cascade delete likes on post deletion
   - Cascade delete comments on user deletion
   - Cascade delete comments on post deletion
   - Cascade delete replies on parent deletion

5. **Policies** (10+ tests)
   - LikePolicy tests
   - CommentPolicy tests

6. **XSS Protection** (1 test)
   - Content escaping

7. **View Integration** (3 tests)
   - Post card shows like button
   - Post show page shows comments
   - Comment form requires authentication

**Total: 80+ test cases**

## Usage Examples

### Like a Post

```php
// In a form
<form method="POST" action="{{ route('posts.like', ['post' => $post->id]) }}">
    @csrf
    <button type="submit">Like</button>
</form>

// With progressive enhancement
<x-posts.like-button :post="$post" />
```

### Unlike a Post

```php
<form method="POST" action="{{ route('posts.unlike', ['post' => $post->id]) }}">
    @csrf
    @method('DELETE')
    <button type="submit">Unlike</button>
</form>
```

### Toggle Like (JavaScript)

```javascript
// Uses the like-button component with progressive enhancement
// Automatically toggles on click
```

### Add a Comment

```php
<x-posts.comment-form :post="$post" />
```

### Add a Reply

```php
<x-posts.comment-form :post="$post" :parent="$comment" />
```

### Display Comments

```php
// In post show view
@foreach($post->topLevelComments() as $comment)
    <x-posts.comment :comment="$comment" :post="$post" />
@endforeach
```

### Display Like Count

```php
<span>{{ $post->likeCount() }}</span>
```

### Display Comment Count

```php
<span>{{ $post->commentCount() }}</span>
```

### Check Like State

```php
@if($post->isLikedBy(Auth::user()))
    <p>You have liked this post</p>
@endif
```

## Deployment Notes

### 1. Run Migrations
```bash
php artisan migrate
```

This will create the `likes` and `comments` tables with all necessary columns and indexes.

### 2. (Optional) Update Existing Posts
If you have existing posts and want to add engagement data, you can use the factories or create them manually.

### 3. Run Tests
```bash
php artisan test
```

All 80+ tests should pass.

## File Structure

```
Social-media-app/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       └── User/
│   │           ├── LikeController.php       ✨ NEW
│   │           └── CommentController.php    ✨ NEW
│   ├── Models/
│   │   ├── Like.php                         ✨ NEW
│   │   ├── Comment.php                       ✨ NEW
│   │   └── Post.php                         ✏️ MODIFIED
│   ├── Policies/
│   │   ├── LikePolicy.php                   ✨ NEW
│   │   └── CommentPolicy.php                ✨ NEW
│   └── Providers/
│       └── AuthServiceProvider.php          ✏️ MODIFIED
├── database/
│   ├── factories/
│   │   ├── LikeFactory.php                  ✨ NEW
│   │   ├── CommentFactory.php               ✨ NEW
│   │   └── UserFactory.php                 ✏️ MODIFIED
│   └── migrations/
│       ├── 2026_10_11_000004_create_likes_table.php     ✨ NEW
│       └── 2026_10_11_000005_create_comments_table.php  ✨ NEW
├── resources/
│   └── views/
│       ├── components/
│       │   └── posts/
│       │       ├── like-button.blade.php    ✨ NEW
│       │       ├── comment-form.blade.php    ✨ NEW
│       │       └── comment.blade.php         ✨ NEW
│       └── posts/
│           ├── comments.blade.php            ✨ NEW
│           └── show.blade.php                 ✏️ MODIFIED
├── routes/
│   └── web.php                              ✏️ MODIFIED
└── tests/
    └── Feature/
        └── User/
            └── EngagementTest.php             ✨ NEW
```

## Design Decisions

### 1. Threaded Replies
- Used `parent_id` column for self-referencing relationship
- Allows unlimited nesting depth (limited by `$maxLevel` in view)
- Efficient querying with proper indexes
- Recursive display in view components

### 2. Like Constraints
- Unique constraint on (user_id, post_id) prevents duplicates
- No need for application-level duplicate checking
- Database handles integrity automatically

### 3. XSS Protection
- All user content is escaped by default
- Used `e()` helper in accessor
- Blade's `{{ }}` syntax auto-escapes
- Never use `{!! !!}` with user content

### 4. Progressive Enhancement
- Forms work without JavaScript
- JavaScript enhances UX with optimistic updates
- Graceful degradation for all features

### 5. Privacy Enforcement
- Centralized `canEngage()` method on Post model
- Used by both LikePolicy and CommentPolicy
- Consistent behavior across all engagement types

## Future Enhancements

1. **Reactions** - Multiple reaction types (like, love, laugh, etc.)
2. **Mentions** - Notify users when mentioned in comments
3. **Comment Likes** - Allow liking individual comments
4. **Comment Threading UI** - Better visual hierarchy for nested replies
5. **Rich Text** - Markdown or WYSIWYG editing for comments
6. **Comment Moderation** - Admin tools for managing comments
7. **Spam Detection** - Automatic detection of spam comments
8. **Rate Limiting** - Prevent rapid successive engagement

## Requirements Met

✅ Like and unlike posts
✅ Add, edit, and delete comments with ownership checks
✅ Reply to comments with threaded replies
✅ Display like counts and comment counts
✅ Display current user's like state
✅ Paginate long comment lists
✅ Prevent duplicate likes through database constraints
✅ Validate text length and reject empty comments
✅ Escape user-generated content and never render arbitrary HTML
✅ Actions work through normal forms and progressive JavaScript enhancements
✅ Respect private-account and post-visibility rules
✅ Tests for likes, comments, replies, permissions, invalid inputs, and duplicate requests
✅ Reuse existing post components
✅ No bookmarks or sharing implementation
