# Routes Reference

## Authentication Routes

| Method | URI | Name | Controller | Middleware |
|--------|-----|------|------------|------------|
| GET | `/` | `home` | Closure | `guest` |
| GET | `/login` | `login` | `LoginController@showLoginForm` | `guest` |
| POST | `/login` | - | `LoginController@login` | `guest`, `throttle.login:5,1` |
| GET | `/register` | `register` | `RegisterController@showRegistrationForm` | `guest` |
| POST | `/register` | - | `RegisterController@register` | `guest` |
| GET | `/forgot-password` | `password.request` | `ForgotPasswordController@showLinkRequestForm` | `guest` |
| POST | `/forgot-password` | `password.email` | `ForgotPasswordController@sendResetLinkEmail` | `guest` |
| GET | `/reset-password/{token}` | `password.reset` | `ResetPasswordController@showResetForm` | `guest` |
| POST | `/reset-password` | `password.update` | `ResetPasswordController@reset` | `guest` |
| POST | `/logout` | `logout` | `LoginController@logout` | `auth` |
| GET | `/email/verify` | `verification.notice` | `VerifyEmailController@showVerifyEmailNotice` | `auth`, `throttle:6,1` |
| GET | `/email/verify/{id}/{hash}` | `verification.verify` | `VerifyEmailController@verify` | `auth`, `signed` |
| POST | `/email/verification-notification` | `verification.send` | `VerifyEmailController@sendVerificationEmail` | `auth`, `throttle:6,1` |

## User Profile Routes

| Method | URI | Name | Controller | Middleware |
|--------|-----|------|------------|------------|
| GET | `/@{user:username}` | `users.profile` | `ProfileController@show` | - |
| GET | `/profile/edit` | `profile.edit` | `ProfileController@edit` | `auth` |
| PUT | `/profile` | `profile.update` | `ProfileController@update` | `auth` |
| POST | `/profile/photo` | `profile.photo.upload` | `ProfileController@uploadProfilePhoto` | `auth` |
| DELETE | `/profile/photo` | `profile.photo.remove` | `ProfileController@removeProfilePhoto` | `auth` |
| DELETE | `/profile/cover` | `profile.cover.remove` | `ProfileController@removeCoverPhoto` | `auth` |

## User Settings Routes

| Method | URI | Name | Controller | Middleware |
|--------|-----|------|------------|------------|
| GET | `/settings` | `settings` | `SettingController@show` | `auth` |
| PUT | `/settings` | `settings.update` | `SettingController@update` | `auth` |
| PUT | `/settings/password` | `settings.password` | `SettingController@updatePassword` | `auth` |
| PUT | `/settings/privacy` | `settings.privacy` | `SettingController@updatePrivacy` | `auth` |

## Other Routes

| Method | URI | Name | Controller | Middleware |
|--------|-----|------|------------|------------|
| GET | `/search` | `search` | Closure | - |
| GET | `/health` | - | Closure | - |
| - | - | - | - | - |

## Named Routes Usage

### Profile Routes
```php
// View user profile
route('users.profile', ['user' => $username])
route('users.profile', ['user' => $user]) // Works with route model binding

// Edit profile
route('profile.edit')

// Update profile
route('profile.update')

// Upload profile photo
route('profile.photo.upload')

// Remove profile photo
route('profile.photo.remove')

// Remove cover photo
route('profile.cover.remove')
```

### Settings Routes
```php
// View settings
route('settings')

// Update settings
route('settings.update')

// Change password
route('settings.password')

// Update privacy
route('settings.privacy')
```

### Authentication Routes
```php
// Login
route('login')

// Registration
route('register')

// Password reset
route('password.request')
route('password.email')
route('password.reset', ['token' => $token])
route('password.update')

// Email verification
route('verification.notice')
route('verification.verify', ['id' => $id, 'hash' => $hash])
route('verification.send')

// Logout
route('logout')
```

## URL Generation in Blade

```blade
<!-- Profile -->
<a href="{{ route('users.profile', ['user' => $user->username]) }}">View Profile</a>
<a href="{{ route('profile.edit') }}">Edit Profile</a>

<!-- Settings -->
<a href="{{ route('settings') }}">Account Settings</a>

<!-- Authentication -->
<a href="{{ route('login') }}">Login</a>
<a href="{{ route('register') }}">Register</a>

<!-- Forms -->
<form method="POST" action="{{ route('logout') }}">
    @csrf
    <button type="submit">Logout</button>
</form>

<form method="PUT" action="{{ route('profile.update') }}">
    @csrf
    @method('PUT')
    <!-- form fields -->
</form>
```

## Route Model Binding

The `@{user:username}` route uses implicit route model binding with the `username` field:

```php
// In controller
public function show(User $user)
{
    // $user is automatically resolved by username
    return view('users.profile', ['user' => $user]);
}
```

## Middleware Groups

### Guest Middleware
- Applied to: `/`, `/login`, `/register`, `/forgot-password`, `/reset-password`
- Redirects authenticated users away

### Auth Middleware
- Applied to: `/logout`, `/profile/edit`, `/profile`, `/settings`, `/settings/*`
- Requires authentication
