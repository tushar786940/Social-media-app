# User Profiles & Account Settings Implementation Summary

## Overview

This document summarizes the implementation of user profiles and account settings for the Social Media App. The implementation follows Laravel best practices and integrates with the existing authentication system.

## Features Implemented

### 1. Database Schema
- **Migration**: `2026_10_11_000001_add_profile_fields_to_users_table.php`
- Added fields to `users` table:
  - `username` (unique, string, max:50) - for public profile URLs
  - `bio` (nullable, text) - user biography
  - `location` (nullable, string, max:100) - user location
  - `website` (nullable, string, max:255) - user website URL
  - `birthdate` (nullable, date) - user birthdate
  - `profile_photo_path` (nullable, string) - path to profile photo
  - `cover_photo_path` (nullable, string) - path to cover photo
  - `is_private` (boolean, default: false) - privacy setting

### 2. User Model Updates
- **File**: `app/Models/User.php`
- Added fillable fields for all new profile attributes
- Added casts for `birthdate` (date) and `is_private` (boolean)
- Added accessors/mutators:
  - `profilePhoto()` - Returns profile photo URL or default avatar
  - `coverPhoto()` - Returns cover photo URL
  - `profileUrl()` - Returns public profile URL
  - `hasProfilePhoto()` - Checks if user has profile photo
  - `hasCoverPhoto()` - Checks if user has cover photo
  - `displayName()` - Returns name or username as fallback
  - `formattedBirthdate()` - Returns formatted birthdate string
  - `age()` - Returns calculated age from birthdate
  - `defaultProfilePhotoUrl()` - Returns default avatar SVG path

### 3. Image Upload Service
- **File**: `app/Services/ImageUploadService.php`
- Safe image upload handling with:
  - File validation (size, MIME type, extension, image validity)
  - Secure filename generation (timestamp + random string)
  - Storage on 'public' disk in organized directories
  - Path validation to prevent directory traversal
  - Support for profile photos and cover photos
  - Methods: `uploadProfilePhoto()`, `uploadCoverPhoto()`, `deleteFile()`
  - Configurable settings: max size (2MB), allowed types (JPEG, PNG, GIF, WEBP)

### 4. Controllers

#### ProfileController (`app/Http/Controllers/User/ProfileController.php`)
- `show(User $user)` - Display public profile page
- `edit()` - Show edit profile form
- `update(Request $request, ImageUploadService $uploadService)` - Update profile information
- `removeProfilePhoto(ImageUploadService $uploadService)` - Remove profile photo
- `removeCoverPhoto(ImageUploadService $uploadService)` - Remove cover photo
- `uploadProfilePhoto(Request $request, ImageUploadService $uploadService)` - Handle profile photo upload

#### SettingController (`app/Http/Controllers/User/SettingController.php`)
- `show()` - Display account settings page
- `update(Request $request)` - Update account settings (name, email)
- `updatePassword(Request $request)` - Change password with current password verification
- `updatePrivacy(Request $request)` - Update privacy settings

### 5. Authorization Policy
- **File**: `app/Policies/UserPolicy.php`
- Defines authorization rules for:
  - `view()` - View profiles (public or own private)
  - `viewPublic()` - View public profiles
  - `edit()` - Edit profiles (owner only)
  - `updateSettings()` - Update settings (owner only)
  - `changePassword()` - Change password (owner only)
  - `updatePrivacy()` - Update privacy (owner only)
  - `uploadPhotos()` - Upload photos (owner only)
  - `deletePhotos()` - Delete photos (owner only)
  - `delete()` - Delete account (owner only)

### 6. Routes
- **File**: `routes/web.php`
- Public routes:
  - `GET /@{user:username}` - View user profile (`users.profile`)
- Authenticated routes:
  - `GET /profile/edit` - Edit profile form (`profile.edit`)
  - `PUT /profile` - Update profile (`profile.update`)
  - `POST /profile/photo` - Upload profile photo (`profile.photo.upload`)
  - `DELETE /profile/photo` - Remove profile photo (`profile.photo.remove`)
  - `DELETE /profile/cover` - Remove cover photo (`profile.cover.remove`)
  - `GET /settings` - View settings (`settings`)
  - `PUT /settings` - Update settings (`settings.update`)
  - `PUT /settings/password` - Change password (`settings.password`)
  - `PUT /settings/privacy` - Update privacy (`settings.privacy`)

### 7. Views

#### Profile Views
- `resources/views/users/profile.blade.php` - Public profile page
  - Displays cover photo, profile photo, user information
  - Shows bio, location, website, birthdate, join date
  - Displays privacy notice for private accounts
  - Edit button for profile owner

- `resources/views/users/edit-profile.blade.php` - Edit profile form
  - Profile photo upload and removal
  - Cover photo upload and removal
  - Profile information form (name, username, bio, location, website, birthdate)
  - Privacy settings toggle

- `resources/views/users/settings.blade.php` - Account settings page
  - Profile settings section (name, email)
  - Password change section with current password verification
  - Danger zone for account deletion (UI only, functionality not implemented)

### 8. User Factory Updates
- **File**: `database/factories/UserFactory.php`
- Added default values for new fields
- Added state methods:
  - `private()` - Create private user
  - `withProfilePhoto()` - Create user with profile photo path
  - `withCoverPhoto()` - Create user with cover photo path

### 9. Default Assets
- **File**: `public/images/default-avatar.svg`
- SVG default avatar used when user has no profile photo

### 10. AuthServiceProvider
- **File**: `app/Providers/AuthServiceProvider.php`
- Registered `UserPolicy` for `User` model

## Security Features

### 1. Image Upload Security
- File size limit: 2MB
- Allowed MIME types: JPEG, PNG, GIF, WEBP
- File extension validation
- Image validity check using `getimagesize()`
- Secure filename generation (prevents filename collisions)
- Path validation (prevents directory traversal attacks)
- Old file cleanup on replacement

### 2. Authorization
- All profile edits require authentication
- Policy-based authorization for all actions
- Ownership enforcement for all profile modifications
- Private accounts cannot be viewed by others (except owner)

### 3. Password Security
- Current password verification required for password changes
- Password confirmation required
- Laravel's default password validation rules applied
- Other sessions invalidated on password change

### 4. Form Validation
- Username: unique, alpha_dash, max:50
- Name: required, string, max:255
- Bio: nullable, string, max:500
- Location: nullable, string, max:100
- Website: nullable, URL, max:255
- Birthdate: nullable, date, not in future
- Email: required, email, unique, lowercase
- Profile/Cover photos: image, MIME types, max:2MB

## Testing

### Test Files Created

1. **ProfileTest.php** (`tests/Feature/User/ProfileTest.php`)
   - Profile page viewing tests
   - Edit profile authorization tests
   - Profile update tests
   - Username uniqueness validation
   - Username format validation
   - Bio length validation
   - Website URL validation
   - Birthdate validation
   - Profile photo upload tests
   - Profile photo removal tests
   - Cover photo upload tests
   - Cover photo removal tests
   - Authorization enforcement tests
   - Default avatar tests
   - Age calculation tests

2. **SettingTest.php** (`tests/Feature/User/SettingTest.php`)
   - Settings page access tests
   - Account settings update tests
   - Password change tests
   - Current password verification tests
   - Password strength validation
   - Password confirmation validation
   - Privacy settings update tests
   - Display name tests
   - Formatted birthdate tests
   - Age calculation tests

3. **UserPolicyTest.php** (`tests/Feature/User/UserPolicyTest.php`)
   - View policy tests
   - Edit policy tests
   - Update settings policy tests
   - Change password policy tests
   - Update privacy policy tests
   - Upload photos policy tests
   - Delete photos policy tests
   - Delete account policy tests

### Test Coverage
- 50+ test cases covering:
  - Successful operations
  - Failed operations
  - Authorization checks
  - Validation errors
  - Edge cases

## Usage Examples

### Viewing a Profile
```php
// In a controller or route
return view('users.profile', ['user' => $user]);

// Or via route
route('users.profile', ['user' => $username])
```

### Updating Profile
```php
// In a form
<form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <input type="text" name="name" value="{{ old('name', $user->name) }}">
    <input type="text" name="username" value="{{ old('username', $user->username) }}">
    <textarea name="bio">{{ old('bio', $user->bio) }}</textarea>
    <input type="file" name="profile_photo">
    <input type="file" name="cover_photo">
    <button type="submit">Save</button>
</form>
```

### Changing Password
```php
<form method="POST" action="{{ route('settings.password') }}">
    @csrf
    @method('PUT')
    <input type="password" name="current_password">
    <input type="password" name="password">
    <input type="password" name="password_confirmation">
    <button type="submit">Change Password</button>
</form>
```

### Uploading Profile Photo
```php
<form method="POST" action="{{ route('profile.photo.upload') }}" enctype="multipart/form-data">
    @csrf
    <input type="file" name="profile_photo" accept="image/*">
    <button type="submit">Upload</button>
</form>
```

### Removing Profile Photo
```php
<form method="POST" action="{{ route('profile.photo.remove') }}">
    @csrf
    @method('DELETE')
    <button type="submit">Remove</button>
</form>
```

## File Structure

```
app/
├── Http/
│   └── Controllers/
│       └── User/
│           ├── ProfileController.php
│           └── SettingController.php
├── Models/
│   └── User.php
├── Policies/
│   └── UserPolicy.php
├── Providers/
│   └── AuthServiceProvider.php
└── Services/
    └── ImageUploadService.php

database/
├── factories/
│   └── UserFactory.php
└── migrations/
    └── 2026_10_11_000001_add_profile_fields_to_users_table.php

public/
└── images/
    └── default-avatar.svg

resources/
└── views/
    └── users/
        ├── profile.blade.php
        ├── edit-profile.blade.php
        └── settings.blade.php

routes/
└── web.php

tests/
└── Feature/
    └── User/
        ├── ProfileTest.php
        ├── SettingTest.php
        └── UserPolicyTest.php
```

## Future Enhancements

1. **Image Processing**: Add Intervention Image for resizing and optimization
2. **Following System**: Implement follower/following relationships
3. **Posts**: Add post creation and display functionality
4. **Notifications**: Add notification system for profile activities
5. **Search**: Implement user search functionality
6. **Rate Limiting**: Add rate limiting for photo uploads
7. **Moderation**: Add profile photo moderation
8. **Custom Domains**: Support custom domain profiles

## Dependencies

The implementation uses only Laravel core features and does not require additional packages. For enhanced image processing, consider adding:

```bash
composer require intervention/image
```

## Notes

- The implementation preserves all existing authentication features
- All new features are properly authorized using Laravel policies
- Image uploads are validated and stored securely
- Default avatar fallback is provided for users without profile photos
- All forms use CSRF protection
- Password changes require current password verification
- Private profiles are only visible to the owner (for now)
