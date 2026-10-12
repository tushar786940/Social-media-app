# Follow System Implementation

## Overview

This document describes the implementation of the follow system for the Social Media App. The system allows users to follow and unfollow other users, with support for private accounts that require follow request approval.

## Features Implemented

### 1. Core Follow Functionality
- **Follow users** - Create follow relationships
- **Unfollow users** - Remove follow relationships
- **Follow requests** - For private accounts, requires approval
- **Accept/Reject requests** - Profile owners can manage follow requests

### 2. Lists & Counts
- **Followers list** - Paginated list of users who follow a profile
- **Following list** - Paginated list of users a profile follows
- **Follower count** - Displayed on profile pages
- **Following count** - Displayed on profile pages
- **Follow status** - Shows Follow/Unfollow/Requested button

### 3. Privacy & Security
- **Private accounts** - Require follow request approval
- **Public accounts** - Auto-accept follow requests
- **Prevent self-follow** - Cannot follow oneself
- **Prevent duplicates** - Database constraint prevents duplicate follows
- **Authorization** - Only authenticated users can follow

### 4. Performance
- **Eager loading** - Avoids N+1 queries for counts
- **Database indexes** - Optimized for common queries
- **Transactions** - Ensures data integrity
- **Pagination** - Followers/following lists are paginated

### 5. User Experience
- **Follow button** - On profile pages for non-owners
- **Unfollow button** - When already following
- **Requested indicator** - For pending follow requests
- **Success/error feedback** - Clear messages for all actions
- **Confirmation** - For destructive actions

## Database Schema

### follows Table

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | bigint (PK) | No | - | Auto-incrementing ID |
| follower_id | bigint (FK) | No | - | User who is following |
| following_id | bigint (FK) | No | - | User being followed |
| status | string | No | 'accepted' | pending/accepted |
| created_at | timestamp | No | - | Creation timestamp |
| updated_at | timestamp | No | - | Last update timestamp |

### Indexes
- `follower_id` - For querying who a user follows
- `following_id` - For querying who follows a user
- `status` - For filtering by status
- `following_id + status` - For querying accepted/pending followers
- `follower_id + following_id` - Unique constraint (prevents duplicates)

### Foreign Keys
- `follower_id` references `users.id` with `CASCADE` on delete
- `following_id` references `users.id` with `CASCADE` on delete

## Models

### Follow Model (`app/Models/Follow.php`)

**Fillable Attributes:**
- `follower_id`
- `following_id`
- `status`

**Relationships:**
- `follower()` - BelongsTo relationship with User (follower)
- `following()` - BelongsTo relationship with User (following)

**Accessors/Methods:**
- `isAccepted()` - Check if follow is accepted
- `isPending()` - Check if follow is pending
- `accept()` - Accept a follow request
- `reject()` - Reject a follow request (deletes the record)

**Scopes:**
- `accepted()` - Filter to accepted follows
- `pending()` - Filter to pending follows
- `byFollower($userId)` - Filter by follower
- `byFollowing($userId)` - Filter by following
- `newest()` - Order by creation date (newest first)
- `oldest()` - Order by creation date (oldest first)

### User Model Updates (`app/Models/User.php`)

**Relationships:**
- `following()` - BelongsToMany: users this user follows
- `followers()` - BelongsToMany: users who follow this user
- `followingAccepted()` - Only accepted follows
- `followersAccepted()` - Only accepted followers
- `followRequests()` - Pending follow requests (for private accounts)

**Methods:**
- `followingCount()` - Count of users this user follows
- `followerCount()` - Count of followers
- `pendingFollowRequestsCount()` - Count of pending follow requests
- `isFollowing(User $user)` - Check if following a specific user
- `isFollowedBy(User $user)` - Check if followed by a specific user
- `hasPendingFollowRequestFor(User $user)` - Check if has pending request for a user
- `hasPendingRequestFrom(User $user)` - Check if has pending request from a user
- `followRelationship(User $user)` - Get the follow relationship with a user
- `canView(User $user)` - Check if can view a user's content (respects privacy)

## Policies

### FollowPolicy (`app/Policies/FollowPolicy.php`)

**Methods:**
- `follow(User $user, User $profileUser)` - Can follow another user (not self)
- `unfollow(User $user, User $profileUser)` - Can unfollow if currently following
- `accept(User $user, User $follower)` - Can accept follow request (if has pending request)
- `reject(User $user, User $follower)` - Can reject follow request (if has pending request)
- `viewFollowers(User $user, User $profileUser)` - Can view followers list
- `viewFollowing(User $user, User $profileUser)` - Can view following list
- `create()` - Alias for follow
- `delete()` - Alias for unfollow

### UserPolicy Updates (`app/Policies/UserPolicy.php`)

**Methods:**
- `viewPosts(User $currentUser, User $profileUser)` - Updated to respect follow status
- `viewPrivatePosts(User $currentUser, User $profileUser)` - For private content access

## Controllers

### FollowController (`app/Http/Controllers/User/FollowController.php`)

**Methods:**
- `follow(Request $request, User $user)` - Follow a user
- `unfollow(Request $request, User $user)` - Unfollow a user
- `accept(Request $request, User $follower)` - Accept follow request
- `reject(Request $request, User $follower)` - Reject follow request
- `followers(Request $request, User $user)` - List followers
- `following(Request $request, User $user)` - List following
- `status(Request $request, User $user)` - Get follow status (JSON)

**Features:**
- Uses database transactions for data integrity
- Validates against self-follow and duplicates
- Handles private account follow requests
- Provides clear success/error feedback
- Respects authorization policies

## Views

### Followers List (`resources/views/users/followers.blade.php`)
- Displays paginated list of followers
- Shows follower avatar, name, username
- Links to follower profiles
- Empty state when no followers
- Respects privacy (only visible to owner or accepted followers for private accounts)

### Following List (`resources/views/users/following.blade.php`)
- Displays paginated list of users being followed
- Shows following user avatar, name, username
- Links to following user profiles
- Unfollow button for profile owner
- Empty state when not following anyone
- Respects privacy (only visible to owner for private accounts)

### Profile Updates (`resources/views/users/profile.blade.php`)
- Follow/Unfollow/Requested button based on current status
- Follower and following counts with links to lists
- Follow button disabled for own profile
- Private account follow request handling

## Routes

### Follow Routes

| Method | URI | Name | Controller | Middleware |
|--------|-----|------|------------|------------|
| POST | `/follow/{user}` | `follow` | `FollowController@follow` | `auth` |
| DELETE | `/follow/{user}` | `unfollow` | `FollowController@unfollow` | `auth` |
| POST | `/follow/{user}/accept` | `follow.accept` | `FollowController@accept` | `auth` |
| POST | `/follow/{user}/reject` | `follow.reject` | `FollowController@reject` | `auth` |
| GET | `/@{user:username}/followers` | `users.followers` | `FollowController@followers` | - |
| GET | `/@{user:username}/following` | `users.following` | `FollowController@following` | - |
| GET | `/follow/status/{user}` | `follow.status` | `FollowController@status` | `auth` |

## Factories

### FollowFactory (`database/factories/FollowFactory.php`)

**Default State:**
- `follower_id` - Random user
- `following_id` - Random user
- `status` - 'accepted'

**State Methods:**
- `pending()` - Creates pending follow
- `accepted()` - Creates accepted follow
- `forUsers(User $follower, User $following)` - Creates follow for specific users

### UserFactory Updates (`database/factories/UserFactory.php`)

**New State Methods:**
- `withFollowers(int $count = 3)` - Creates user with specified number of followers
- `withFollowing(int $count = 3)` - Creates user with specified number of following

## Validation & Authorization

### Validation Rules
- Follow: User must be authenticated, cannot follow self, no duplicate follows
- Unfollow: User must be authenticated, cannot unfollow self, must be following
- Accept: Must have pending follow request from the follower
- Reject: Must have pending follow request from the follower

### Authorization Rules
- **Follow**: Any authenticated user can attempt to follow (except self)
- **Unfollow**: Only if currently following
- **Accept/Reject**: Only profile owner can accept/reject requests
- **View Followers**: Public accounts - anyone; Private accounts - owner and accepted followers
- **View Following**: Public accounts - anyone; Private accounts - owner only

## Performance Optimizations

### Eager Loading
- Profile pages use `loadCount()` for follower/following counts
- Avoids N+1 queries when displaying multiple profiles

### Database Indexes
- Indexes on `follower_id`, `following_id`, `status` for fast queries
- Composite indexes for common query patterns

### Transactions
- Follow/unfollow/accept/reject operations use database transactions
- Ensures data integrity even if multiple operations fail

## Testing

### Test File: `tests/Feature/User/FollowTest.php`

**Test Categories:**

1. **Follow Creation** (6 tests)
   - Follow public user
   - Create pending follow for private user
   - Prevent following oneself
   - Prevent duplicate follow for public user
   - Prevent duplicate follow request for private user
   - Cannot follow when not authenticated

2. **Unfollow** (5 tests)
   - Unfollow a user
   - Cancel pending follow request
   - Prevent unfollowing oneself
   - Prevent unfollowing user not following
   - Cannot unfollow when not authenticated

3. **Follow Request Management** (5 tests)
   - Accept follow request
   - Reject follow request
   - Cannot accept non-existent follow request
   - Cannot accept follow request for non-private account
   - Cannot accept own follow request

4. **Followers List** (5 tests)
   - View followers of public user
   - View followers of private user when owner
   - View followers of private user when accepted follower
   - Cannot view followers of private user when not follower
   - Shows empty state when no followers

5. **Following List** (5 tests)
   - View following of public user
   - View following of private user when owner
   - Cannot view following of private user when not owner
   - Shows empty state when not following anyone

6. **User Model Relationships** (12 tests)
   - Has following relationship
   - Has followers relationship
   - Has followingCount method
   - Has followerCount method
   - Has isFollowing method
   - Has isFollowedBy method
   - Has hasPendingFollowRequestFor method
   - Has hasPendingRequestFrom method
   - Self-follow checks
   - canView method for privacy

7. **Follow Model** (7 tests)
   - Has follower relationship
   - Has following relationship
   - Has isAccepted/isPending methods
   - Has accept/reject methods
   - Has scopes

8. **Database Constraints** (2 tests)
   - Prevents duplicate follow records
   - Cascades delete on user deletion

9. **Profile Page Integration** (5 tests)
   - Shows follow button on profile page
   - Shows unfollow button when already following
   - Shows requested button for pending requests
   - Shows follower and following counts
   - Does not show follow button on own profile

**Total: 50+ test cases**

## Usage Examples

### Following a User

```php
// In a form or controller
<form method="POST" action="{{ route('follow', ['user' => $user->username]) }}">
    @csrf
    <button type="submit">Follow</button>
</form>
```

### Unfollowing a User

```php
<form method="POST" action="{{ route('unfollow', ['user' => $user->username]) }}">
    @csrf
    @method('DELETE')
    <button type="submit">Unfollow</button>
</form>
```

### Accepting a Follow Request

```php
<form method="POST" action="{{ route('follow.accept', ['user' => $follower->username]) }}">
    @csrf
    <button type="submit">Accept</button>
</form>
```

### Rejecting a Follow Request

```php
<form method="POST" action="{{ route('follow.reject', ['user' => $follower->username]) }}">
    @csrf
    <button type="submit">Reject</button>
</form>
```

### Checking Follow Status

```php
// In a controller or view
if ($currentUser->isFollowing($profileUser)) {
    // User is following
} elseif ($currentUser->hasPendingFollowRequestFor($profileUser)) {
    // User has pending request
} else {
    // User is not following
}
```

### Displaying Follower/Following Counts

```blade
<p>Followers: {{ $user->followerCount() }}</p>
<p>Following: {{ $user->followingCount() }}</p>
```

### Linking to Followers/Following Lists

```blade
<a href="{{ route('users.followers', ['user' => $user->username]) }}">
    {{ $user->followerCount() }} followers
</a>

a href="{{ route('users.following', ['user' => $user->username]) }}">
    {{ $user->followingCount() }} following
</a>
```

## Deployment Notes

### 1. Run Migration
```bash
php artisan migrate
```

This will create the `follows` table with all necessary columns and indexes.

### 2. (Optional) Update Existing Users
If you have existing users and want to add follow relationships, you can use the factories or create them manually.

### 3. Run Tests
```bash
php artisan test
```

All 50+ tests should pass.

## File Structure

```
Social-media-app/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       └── User/
│   │           └── FollowController.php      ✨ NEW
│   ├── Models/
│   │   ├── Follow.php                        ✨ NEW
│   │   └── User.php                        ✏️ MODIFIED
│   ├── Policies/
│   │   ├── FollowPolicy.php                  ✨ NEW
│   │   └── UserPolicy.php                  ✏️ MODIFIED
│   └── Providers/
│       └── AuthServiceProvider.php          ✏️ MODIFIED
├── database/
│   ├── factories/
│   │   ├── FollowFactory.php                 ✨ NEW
│   │   └── UserFactory.php                 ✏️ MODIFIED
│   └── migrations/
│       └── 2026_10_11_000003_create_follows_table.php  ✨ NEW
├── resources/
│   └── views/
│       └── users/
│           ├── followers.blade.php           ✨ NEW
│           ├── following.blade.php           ✨ NEW
│           └── profile.blade.php             ✏️ MODIFIED
├── routes/
│   └── web.php                             ✏️ MODIFIED
└── tests/
    └── Feature/
        └── User/
            └── FollowTest.php                ✨ NEW
```

## Design Decisions

### 1. Many-to-Many Relationship
- Used Laravel's `belongsToMany` relationship for follows
- Pivot table with `status` column for pending/accepted states
- Allows efficient querying of followers and following

### 2. Status Field
- `status` column with 'accepted' or 'pending' values
- Public accounts: auto-accept (status = 'accepted')
- Private accounts: create pending request (status = 'pending')
- Profile owner can accept/reject pending requests

### 3. Database Constraints
- Unique constraint on `(follower_id, following_id)` prevents duplicates
- Foreign keys with `CASCADE` ensure cleanup when users are deleted
- Indexes optimize common queries

### 4. Authorization
- FollowPolicy handles all authorization logic
- UserPolicy updated to respect follow status for private content
- Clear separation of concerns

### 5. Eager Loading
- Used `loadCount()` for follower/following counts
- Avoids N+1 query problem
- Efficient for profile pages

## Future Enhancements

1. **Notifications** - Notify users when they gain new followers
2. **Feed Algorithm** - Use follow relationships to build user's feed
3. **Mutual Follows** - Show mutual follow indicators
4. **Follow Suggestions** - Suggest users to follow
5. **Block System** - Allow users to block other users
6. **Follow Analytics** - Track follower growth over time
7. **Import/Export** - Allow users to import/export their follow lists

## Requirements Met

✅ Follow and unfollow users
✅ Followers and following lists with pagination
✅ Follower/following counts
✅ Follow status displayed on profile pages
✅ Prevent following oneself
✅ Prevent duplicate follow records
✅ Enforce private-account rules (follow requests)
✅ Use database constraints and transactions
✅ Protect actions with authentication, validation, authorization, CSRF
✅ Many-to-many relationship with maintainable structure
✅ Eager loading to avoid N+1 queries
✅ Tests for following, unfollowing, duplicate actions, private profiles, unauthenticated requests, self-follow attempts
✅ Integrated with existing profile pages
✅ No feed implementation
