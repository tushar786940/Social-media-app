# Posts Module Implementation

## Overview

This document describes the implementation of the post creation and management module for the Social Media App. The module allows users to create, edit, delete, and view posts with text content and optional image attachments.

## Features Implemented

### 1. Post Creation
- Create posts with text content (max 5000 characters)
- Upload optional image attachments (JPEG, PNG, GIF, WEBP)
- Set post visibility (public or private)
- Automatic visibility default based on user's account privacy setting

### 2. Post Management
- Edit post content, image, and visibility
- Delete posts with associated image cleanup
- Remove images from posts without deleting the post
- Ownership enforcement for all actions

### 3. Post Display
- Post detail pages with full information
- Reusable post card component for feeds
- Author information with profile links
- Timestamps (absolute and relative)
- Visibility indicators

### 4. Visibility System
- Public posts: visible to everyone
- Private posts: visible only to the author
- Consistent with account privacy model

### 5. Security & Validation
- Server-side validation for all inputs
- CSRF protection on all forms
- Authorization policies for all actions
- Mass assignment protection
- Safe image upload and storage
- File cleanup on post deletion

### 6. User Experience
- Pagination for post listings
- Clear success/error feedback
- Confirmation for destructive actions
- Responsive design

## Database Schema

### posts Table

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | bigint (PK) | No | - | Auto-incrementing ID |
| user_id | bigint (FK) | No | - | User who created the post |
| content | text | No | - | Post text content |
| image_path | string | Yes | NULL | Path to uploaded image |
| visibility | string | No | 'public' | Post visibility (public/private) |
| created_at | timestamp | No | - | Creation timestamp |
| updated_at | timestamp | No | - | Last update timestamp |

### Indexes
- `user_id` - For querying posts by user
- `created_at` - For chronological ordering
- `visibility` - For filtering by visibility
- `user_id + created_at` - For paginated user post queries

### Foreign Keys
- `user_id` references `users.id` with `CASCADE` on delete

## Models

### Post Model (`app/Models/Post.php`)

**Fillable Attributes:**
- `content`
- `image_path`
- `visibility`

**Relationships:**
- `user()` - BelongsTo relationship with User

**Accessors:**
- `imageUrl()` - Returns the full URL to the post image
- `hasImage()` - Checks if post has an image
- `formattedDate()` - Returns formatted creation date
- `relativeDate()` - Returns relative time (e.g., "2 hours ago")
- `visibilityLabel()` - Returns human-readable visibility label

**Methods:**
- `url()` - Returns the post detail page URL
- `editUrl()` - Returns the post edit page URL
- `authorName()` - Returns the author's display name
- `authorUsername()` - Returns the author's username
- `authorUrl()` - Returns the author's profile URL
- `authorPhoto()` - Returns the author's profile photo URL

**Scopes:**
- `public()` - Filters to only public posts
- `forUser($userId)` - Filters to posts by a specific user
- `newest()` - Orders by creation date (newest first)

### User Model Updates (`app/Models/User.php`)

**Relationships:**
- `posts()` - HasMany relationship with Post
- `publicPosts()` - Returns only public posts by the user

**Methods:**
- `postCount()` - Returns the total number of posts by the user

## Policies

### PostPolicy (`app/Policies/PostPolicy.php`)

**Methods:**
- `view(User $user, Post $post)` - Can view post based on visibility
- `viewAny(User $user)` - Can view any posts (always true)
- `create(User $user)` - Can create posts (if authenticated)
- `update(User $user, Post $post)` - Can edit post (owner only)
- `delete(User $user, Post $post)` - Can delete post (owner only)
- `restore(User $user, Post $post)` - Can restore post (owner only)
- `forceDelete(User $user, Post $post)` - Can force delete post (owner only)
- `uploadImage(User $user, Post $post)` - Can upload images (owner only)
- `deleteImage(User $user, Post $post)` - Can delete images (owner only)
- `defaultVisibility(User $user)` - Returns default visibility for user

### UserPolicy Updates (`app/Policies/UserPolicy.php`)

**Methods:**
- `viewPosts(User $currentUser, User $profileUser)` - Can view user's posts
- `viewPrivatePosts(User $currentUser, User $profileUser)` - Can view user's private posts

## Services

### ImageUploadService Updates (`app/Services/ImageUploadService.php`)

**New Method:**
- `uploadPostImage(UploadedFile $file, ?string $oldPath = null)` - Uploads and processes post images

**Updated:**
- `isValidPath()` - Now includes post-images directory
- Added `$postImagePath` constant

## Controllers

### PostController (`app/Http/Controllers/User/PostController.php`)

**Methods:**
- `index(Request $request)` - Display paginated list of posts
- `userPosts(Request $request, $username)` - Display posts for a specific user
- `create(Request $request)` - Show create post form
- `store(Request $request, ImageUploadService $uploadService)` - Store new post
- `show(Post $post)` - Display post detail page
- `edit(Post $post)` - Show edit post form
- `update(Request $request, Post $post, ImageUploadService $uploadService)` - Update post
- `destroy(Post $post, ImageUploadService $uploadService)` - Delete post
- `removeImage(Post $post, ImageUploadService $uploadService)` - Remove post image

**Authorization:**
- All methods use Laravel's `authorize()` helper
- Policies enforce ownership and visibility rules

## Views

### Components

#### Post Card (`resources/views/components/posts/card.blade.php`)
- Reusable component for displaying posts
- Shows author info, content, image, timestamps
- Shows edit/delete buttons for post owner
- Props: `post`, `showActions`

### Pages

#### Posts Index (`resources/views/posts/index.blade.php`)
- Displays paginated list of posts
- Shows "Create Post" button for authenticated users
- Shows empty state when no posts exist

#### Post Create (`resources/views/posts/create.blade.php`)
- Form for creating new posts
- Fields: content (textarea), image (file upload), visibility (select)
- Default visibility based on user's account privacy

#### Post Edit (`resources/views/posts/edit.blade.php`)
- Form for editing existing posts
- Shows current image with remove option
- Fields: content, image replacement, visibility
- Cancel and Update buttons

#### Post Show (`resources/views/posts/show.blade.php`)
- Displays single post in detail
- Shows post card with full information
- Shows edit/delete actions for post owner
- Back to feed link

### Profile Updates

#### User Profile (`resources/views/users/profile.blade.php`)
- Updated to show user's posts
- Shows post count in stats
- Displays paginated list of user's posts

#### Header Updates (`resources/views/layouts/partials/header.blade.php`)
- Added "Posts" link to navigation
- Added "Create Post" button for authenticated users

## Routes

### Post Routes

| Method | URI | Name | Controller | Middleware |
|--------|-----|------|------------|------------|
| GET | `/posts` | `posts.index` | `PostController@index` | `auth` (optional) |
| GET | `/posts/create` | `posts.create` | `PostController@create` | `auth` |
| POST | `/posts` | `posts.store` | `PostController@store` | `auth` |
| GET | `/posts/{post}` | `posts.show` | `PostController@show` | - |
| GET | `/posts/{post}/edit` | `posts.edit` | `PostController@edit` | `auth` |
| PUT | `/posts/{post}` | `posts.update` | `PostController@update` | `auth` |
| DELETE | `/posts/{post}` | `posts.destroy` | `PostController@destroy` | `auth` |
| DELETE | `/posts/{post}/image` | `posts.image.remove` | `PostController@removeImage` | `auth` |

### Profile Route Updates

| Method | URI | Name | Controller | Middleware |
|--------|-----|------|------------|------------|
| GET | `/@{user:username}` | `users.profile` | `ProfileController@show` | - |

The profile route now shows the user's posts when available.

## Factories

### PostFactory (`database/factories/PostFactory.php`)

**Default State:**
- `user_id` - Random user
- `content` - 3 paragraphs of text
- `image_path` - NULL
- `visibility` - 'public'

**State Methods:**
- `withImage()` - Creates post with image path
- `private()` - Creates private post
- `short()` - Creates post with short content
- `long()` - Creates post with long content (10 paragraphs)
- `forUser(User $user)` - Creates post for specific user

### UserFactory Updates (`database/factories/UserFactory.php`)

**New State Method:**
- `withPosts(int $count = 3)` - Creates user with specified number of posts

## Validation Rules

### Post Creation/Update
- `content` - Required, string, max:5000 characters
- `image` - Nullable, image, MIME types: jpeg,png,jpg,gif,webp, max:2048KB
- `visibility` - Required, string, in: ['public', 'private']

### Image Upload
- File type: JPEG, PNG, GIF, WEBP
- Maximum size: 2MB
- Valid image check using `getimagesize()`

## Authorization Rules

### Viewing Posts
- Public posts: viewable by everyone
- Private posts: viewable only by the author
- User's posts: visible based on account privacy and post visibility

### Creating Posts
- Only authenticated users can create posts
- Default visibility matches user's account privacy setting

### Editing/Deleting Posts
- Only the post owner can edit or delete a post
- Only the post owner can remove the post image

### Image Management
- Only the post owner can upload or delete images
- Old images are automatically deleted when replaced
- Images are deleted when the post is deleted

## File Storage

### Structure
```
public/
└── storage/
    └── post-images/
        ├── post_20261011120000_random.jpg
        └── post_20261011120100_random.png
```

### Storage Configuration
- Disk: `public`
- Path: `post-images`
- Files are stored with generated filenames
- Symlink: `public/storage` -> `storage/app/public`

### Cleanup
- Old images are deleted when replaced
- Post images are deleted when post is deleted
- Files are only deleted if they exist and are in allowed directories

## Testing

### Test File: `tests/Feature/User/PostTest.php`

**Test Categories:**

1. **Post Creation** (8 tests)
   - Create post with text only
   - Create post with image
   - Create private post
   - Validate content is required
   - Validate content max length
   - Validate visibility is required
   - Validate visibility is in allowed values
   - Validate image file type
   - Validate image file size
   - Cannot create post when not authenticated

2. **Post Viewing** (5 tests)
   - View public post
   - View own private post
   - Cannot view other users private post
   - View posts index
   - View create post form when authenticated
   - Cannot view create post form when not authenticated

3. **Post Editing** (7 tests)
   - Edit own post
   - Update own post
   - Update post visibility
   - Replace post image
   - Cannot edit other users post
   - Cannot update other users post
   - Validate update content is required

4. **Post Deletion** (4 tests)
   - Delete own post
   - Delete image when post is deleted
   - Cannot delete other users post
   - Cannot delete post when not authenticated

5. **Image Management** (2 tests)
   - Remove post image
   - Cannot remove image from other users post

6. **Post Model** (7 tests)
   - Has user relationship
   - Has image URL accessor
   - Has hasImage method
   - Has formatted date
   - Has relative date
   - Has visibility label
   - Has author accessors

7. **User Model Relationships** (3 tests)
   - Has posts relationship
   - Has publicPosts method
   - Has postCount method

8. **Post Scopes** (3 tests)
   - Has public scope
   - Has forUser scope
   - Has newest scope

9. **Pagination** (1 test)
   - Paginates posts on index

**Total: 40+ test cases**

## Usage Examples

### Creating a Post

```php
// In a form
<form method="POST" action="{{ route('posts.store') }}" enctype="multipart/form-data">
    @csrf
    <textarea name="content" required></textarea>
    <input type="file" name="image" accept="image/*">
    <select name="visibility">
        <option value="public">Public</option>
        <option value="private">Private</option>
    </select>
    <button type="submit">Create Post</button>
</form>
```

### Displaying Posts

```php
// In a view
@foreach($posts as $post)
    <x-posts.card :post="$post" :showActions="Auth::id() === $post->user_id" />
@endforeach

{{ $posts->links() }}
```

### Displaying a Single Post

```php
// In a controller
$post = Post::findOrFail($id);
$this->authorize('view', $post);
return view('posts.show', ['post' => $post]);
```

### Editing a Post

```php
// In a form
<form method="POST" action="{{ route('posts.update', ['post' => $post->id]) }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <textarea name="content">{{ old('content', $post->content) }}</textarea>
    <input type="file" name="image">
    <select name="visibility">
        <option value="public" {{ old('visibility', $post->visibility) === 'public' ? 'selected' : '' }}>Public</option>
        <option value="private" {{ old('visibility', $post->visibility) === 'private' ? 'selected' : '' }}>Private</option>
    </select>
    <button type="submit">Update Post</button>
</form>
```

### Deleting a Post

```php
<form method="POST" action="{{ route('posts.destroy', ['post' => $post->id]) }}">
    @csrf
    @method('DELETE')
    <button type="submit" onclick="return confirm('Are you sure?')">
        Delete Post
    </button>
</form>
```

## Deployment Notes

### 1. Run Migrations
```bash
php artisan migrate
```

### 2. Create Storage Symlink
```bash
php artisan storage:link
```

### 3. Ensure Storage is Writable
```bash
chmod -R 775 storage
chmod -R 775 public/storage
```

### 4. (Optional) Install Image Processing Package
For better image handling (resizing, optimization):
```bash
composer require intervention/image
```

### 5. Run Tests
```bash
php artisan test
```

## File Structure

```
app/
├── Http/
│   └── Controllers/
│       └── User/
│           └── PostController.php
├── Models/
│   ├── Post.php
│   └── User.php (updated)
├── Policies/
│   ├── PostPolicy.php
│   └── UserPolicy.php (updated)
├── Providers/
│   └── AuthServiceProvider.php (updated)
└── Services/
    └── ImageUploadService.php (updated)

database/
├── factories/
│   └── PostFactory.php
└── migrations/
    └── 2026_10_11_000002_create_posts_table.php

public/
└── storage/
    └── post-images/ (created automatically)

resources/
└── views/
    ├── components/
    │   └── posts/
    │       └── card.blade.php
    └── posts/
        ├── index.blade.php
        ├── create.blade.php
        ├── edit.blade.php
        └── show.blade.php

tests/
└── Feature/
    └── User/
        └── PostTest.php

routes/
└── web.php (updated)
```

## Future Enhancements

1. **Image Processing** - Add resizing and optimization using Intervention Image
2. **Rich Text Editing** - Add Markdown or WYSIWYG editor for post content
3. **Post Categories/Tags** - Add categorization and tagging system
4. **Draft Posts** - Allow saving posts as drafts
5. **Scheduled Posts** - Allow scheduling posts for future publication
6. **Post Analytics** - Add view counts and engagement metrics
7. **Reporting** - Add post reporting system
8. **Moderation** - Add admin moderation tools

## Requirements Met

✅ Create posts with text and optional image attachments
✅ Edit and delete only posts owned by the authenticated user
✅ Post detail pages and reusable post-card components
✅ Timestamps and author information
✅ Post visibility options consistent with the account privacy model
✅ Server-side validation, pagination where applicable, and clear success/error feedback
✅ Safe image upload and deletion using Laravel storage
✅ Correct ownership policies and mass-assignment protection
✅ Appropriate database relationships and indexes
✅ Avoid storing uploaded image binaries directly in ordinary database columns
✅ Clean up files when posts are deleted, without deleting files referenced by other records
✅ Tests for creation, editing, deletion, invalid uploads, and attempts to manipulate another user's posts
✅ No likes or comments implementation
