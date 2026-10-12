# Social Media App

A modern, feature-rich social media application built with Laravel and Blade templates. This project implements a complete social media platform with authentication, user profiles, posts, and a follow system.

## 🚀 About the Project

This is a Laravel-based social media application that allows users to:
- Register, login, and manage their accounts
- Create and customize user profiles with photos and information
- Create, edit, and delete posts with text and images
- Follow and unfollow other users
- View profiles, posts, and follow relationships

The application follows Laravel best practices, uses proper authorization and validation, and includes comprehensive test coverage.

## ⚙️ Built With

- **Backend**: [Laravel 13](https://laravel.com/) - PHP Framework
- **Frontend**: Blade templates with Tailwind CSS (via Vite)
- **Database**: MySQL/PostgreSQL/SQLite (configurable)
- **Storage**: Laravel Storage (local disk by default, configurable for S3)
- **Authentication**: Laravel's built-in authentication with custom enhancements
- **Testing**: Pest PHP testing framework

## 📦 Features

### ✅ Authentication Module
- **Registration** - User sign-up with validation
- **Login/Logout** - Secure authentication with CSRF protection
- **Password Reset** - Forgot password and reset functionality
- **Email Verification** - Email confirmation for new accounts
- **Login Throttling** - Protection against brute force attacks
- **Session Management** - Secure session handling

### ✅ User Profiles
- **Public Profile Pages** - View user information at `@username` URLs
- **Profile Information** - Display name, username, bio, location, website, birthdate
- **Profile Photos** - Upload and manage profile pictures
- **Cover Photos** - Upload and manage cover images
- **Privacy Settings** - Public or private account options
- **Default Avatar** - Automatic fallback avatar for users without profile photos

### ✅ Account Settings
- **Edit Profile** - Update profile information
- **Change Password** - Secure password updates with current password verification
- **Update Privacy** - Toggle between public and private account
- **Delete Photos** - Remove profile or cover photos

### ✅ Posts Module
- **Create Posts** - Share text content with optional images
- **Edit Posts** - Update post content, images, and visibility
- **Delete Posts** - Remove posts with automatic image cleanup
- **Post Visibility** - Public or private posts
- **Post Detail Pages** - Individual post pages with full information
- **Reusable Components** - Post card component for consistent display
- **Pagination** - Posts feed with pagination support

### ✅ Follow System
- **Follow Users** - Direct follow for public accounts
- **Follow Requests** - Request system for private accounts
- **Unfollow Users** - Remove follow relationships
- **Accept/Reject Requests** - Manage follow requests for private accounts
- **Followers List** - Paginated list of who follows you
- **Following List** - Paginated list of who you follow
- **Follow Counts** - Displayed on profile pages
- **Follow Status** - Follow/Unfollow/Requested buttons based on current state

## 📁 Project Structure

```
social-media-app/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       ├── Auth/              # Authentication controllers
│   │       │   ├── LoginController.php
│   │       │   ├── RegisterController.php
│   │       │   ├── ForgotPasswordController.php
│   │       │   ├── ResetPasswordController.php
│   │       │   └── VerifyEmailController.php
│   │       └── User/              # User-related controllers
│   │           ├── ProfileController.php
│   │           ├── SettingController.php
│   │           ├── PostController.php
│   │           └── FollowController.php
│   ├── Models/
│   │   ├── User.php              # User model with relationships
│   │   ├── Post.php              # Post model
│   │   └── Follow.php            # Follow model
│   ├── Policies/
│   │   ├── UserPolicy.php        # User authorization policies
│   │   ├── PostPolicy.php        # Post authorization policies
│   │   └── FollowPolicy.php      # Follow authorization policies
│   ├── Providers/
│   │   └── AuthServiceProvider.php
│   └── Services/
│       └── ImageUploadService.php # Image upload handling
├── bootstrap/
│   └── app.php
├── config/
├── database/
│   ├── factories/
│   │   ├── UserFactory.php       # User factory for testing
│   │   ├── PostFactory.php       # Post factory for testing
│   │   └── FollowFactory.php     # Follow factory for testing
│   └── migrations/
│       ├── 2026_10_11_000001_add_profile_fields_to_users_table.php
│       ├── 2026_10_11_000002_create_posts_table.php
│       └── 2026_10_11_000003_create_follows_table.php
├── public/
│   ├── images/
│   │   ├── default-avatar.svg    # Default user avatar
│   │   └── default-avatar.png    # Fallback avatar
│   └── storage/                  # Public storage (symlink)
├── resources/
│   ├── css/
│   │   └── app.css              # Tailwind CSS
│   ├── js/
│   │   └── app.js               # JavaScript
│   └── views/
│       ├── components/
│       │   ├── alerts/
│       │   │   └── flash.blade.php
│       │   ├── buttons/
│       │   │   ├── primary.blade.php
│       │   │   ├── secondary.blade.php
│       │   │   └── danger.blade.php
│       │   ├── cards/
│       │   │   └── base.blade.php
│       │   ├── forms/
│       │   │   ├── input.blade.php
│       │   │   ├── textarea.blade.php
│       │   │   ├── select.blade.php
│       │   │   ├── checkbox.blade.php
│       │   │   └── file.blade.php
│       │   ├── icons/
│       │   │   └── placeholder.blade.php
│       │   └── posts/
│       │       └── card.blade.php  # Reusable post card component
│       ├── layouts/
│       │   ├── app.blade.php       # Main layout
│       │   └── partials/
│       │       ├── header.blade.php
│       │       └── footer.blade.php
│       ├── pages/
│       │   └── home.blade.php
│       ├── auth/
│       │   ├── login.blade.php
│       │   ├── register.blade.php
│       │   └── passwords/
│       │       ├── email.blade.php
│       │       └── reset.blade.php
│       ├── users/
│       │   ├── profile.blade.php   # User profile page
│       │   ├── edit-profile.blade.php
│       │   ├── settings.blade.php
│       │   ├── followers.blade.php  # Followers list
│       │   └── following.blade.php  # Following list
│       └── posts/
│           ├── index.blade.php   # Posts feed
│           ├── create.blade.php  # Create post form
│           ├── edit.blade.php    # Edit post form
│           └── show.blade.php    # Post detail page
├── routes/
│   └── web.php                   # All web routes
├── storage/
├── tests/
│   └── Feature/
│       └── User/
│           ├── ProfileTest.php
│           ├── SettingTest.php
│           ├── PostTest.php
│           ├── FollowTest.php
│           └── UserPolicyTest.php
├── .env.example
├── composer.json
├── package.json
├── vite.config.js
└── README.md                   # This file
```

## 🛠️ Installation

### Prerequisites

- PHP 8.3 or higher
- Composer 2.x
- Node.js 18.x or higher (for Vite)
- MySQL, PostgreSQL, or SQLite
- Git

### Setup

1. **Clone the repository:**
   ```bash
   git clone https://github.com/tushar786940/Social-media-app.git
   cd Social-media-app
   ```

2. **Install PHP dependencies:**
   ```bash
   composer install
   ```

3. **Install Node dependencies:**
   ```bash
   npm install
   ```

4. **Copy environment file:**
   ```bash
   cp .env.example .env
   ```

5. **Generate application key:**
   ```bash
   php artisan key:generate
   ```

6. **Configure database:**
   Edit the `.env` file and set your database connection:
   ```ini
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=social_media
   DB_USERNAME=root
   DB_PASSWORD=
   ```

7. **Run migrations:**
   ```bash
   php artisan migrate
   ```

8. **Create storage symlink:**
   ```bash
   php artisan storage:link
   ```

9. **Build assets:**
   ```bash
   npm run build
   ```

10. **Start development server:**
    ```bash
    php artisan serve
    ```

11. **Access the application:**
    Open [http://localhost:8000](http://localhost:8000) in your browser.

## 🚀 Usage

### Authentication

- **Register**: Create a new account at `/register`
- **Login**: Sign in at `/login`
- **Logout**: Sign out from any page
- **Forgot Password**: Request password reset at `/forgot-password`
- **Reset Password**: Set new password with token from email

### User Profiles

- **View Profile**: Visit `/@username` to see a user's profile
- **Edit Profile**: Update your profile at `/profile/edit`
- **Account Settings**: Manage settings at `/settings`
- **Change Password**: Update password at `/settings` (requires current password)

### Posts

- **Create Post**: Share content at `/posts/create`
- **View Posts**: See all posts at `/posts`
- **View Post**: See individual post at `/posts/{id}`
- **Edit Post**: Update post at `/posts/{id}/edit`
- **Delete Post**: Remove post (with image cleanup)

### Follow System

- **Follow User**: Click "Follow" button on any profile
- **Unfollow User**: Click "Unfollow" button on profiles you follow
- **View Followers**: Click follower count on profile page
- **View Following**: Click following count on profile page
- **Accept/Reject Requests**: For private accounts, manage follow requests

## 🎯 Routes

### Authentication Routes

| Method | URI | Name | Description |
|--------|-----|------|-------------|
| GET | `/` | `home` | Home page |
| GET | `/login` | `login` | Login form |
| POST | `/login` | - | Login submission |
| GET | `/register` | `register` | Registration form |
| POST | `/register` | - | Registration submission |
| GET | `/forgot-password` | `password.request` | Forgot password form |
| POST | `/forgot-password` | `password.email` | Send reset link |
| GET | `/reset-password/{token}` | `password.reset` | Reset password form |
| POST | `/reset-password` | `password.update` | Reset password submission |
| POST | `/logout` | `logout` | Logout |
| GET | `/email/verify` | `verification.notice` | Email verification notice |
| GET | `/email/verify/{id}/{hash}` | `verification.verify` | Verify email |
| POST | `/email/verification-notification` | `verification.send` | Resend verification email |

### User Profile Routes

| Method | URI | Name | Description |
|--------|-----|------|-------------|
| GET | `/@{user:username}` | `users.profile` | View user profile |
| GET | `/profile/edit` | `profile.edit` | Edit profile form |
| PUT | `/profile` | `profile.update` | Update profile |
| POST | `/profile/photo` | `profile.photo.upload` | Upload profile photo |
| DELETE | `/profile/photo` | `profile.photo.remove` | Remove profile photo |
| DELETE | `/profile/cover` | `profile.cover.remove` | Remove cover photo |

### Settings Routes

| Method | URI | Name | Description |
|--------|-----|------|-------------|
| GET | `/settings` | `settings` | Account settings |
| PUT | `/settings` | `settings.update` | Update settings |
| PUT | `/settings/password` | `settings.password` | Change password |
| PUT | `/settings/privacy` | `settings.privacy` | Update privacy |

### Post Routes

| Method | URI | Name | Description |
|--------|-----|------|-------------|
| GET | `/posts` | `posts.index` | Posts feed |
| GET | `/posts/create` | `posts.create` | Create post form |
| POST | `/posts` | `posts.store` | Create post |
| GET | `/posts/{post}` | `posts.show` | View post |
| GET | `/posts/{post}/edit` | `posts.edit` | Edit post form |
| PUT | `/posts/{post}` | `posts.update` | Update post |
| DELETE | `/posts/{post}` | `posts.destroy` | Delete post |
| DELETE | `/posts/{post}/image` | `posts.image.remove` | Remove post image |

### Follow Routes

| Method | URI | Name | Description |
|--------|-----|------|-------------|
| POST | `/follow/{user}` | `follow` | Follow a user |
| DELETE | `/follow/{user}` | `unfollow` | Unfollow a user |
| POST | `/follow/{user}/accept` | `follow.accept` | Accept follow request |
| POST | `/follow/{user}/reject` | `follow.reject` | Reject follow request |
| GET | `/@{user:username}/followers` | `users.followers` | View followers |
| GET | `/@{user:username}/following` | `users.following` | View following |
| GET | `/follow/status/{user}` | `follow.status` | Get follow status |

## 📊 Database Schema

### users Table

| Column | Type | Description |
|--------|------|-------------|
| id | bigint (PK) | Auto-incrementing ID |
| name | string | Display name |
| username | string | Unique username for URLs |
| email | string | User's email |
| password | string | Hashed password |
| bio | text | User biography |
| location | string | User location |
| website | string | User website |
| birthdate | date | User's birthdate |
| profile_photo_path | string | Path to profile photo |
| cover_photo_path | string | Path to cover photo |
| is_private | boolean | Privacy setting |
| email_verified_at | timestamp | Email verification timestamp |
| remember_token | string | Remember me token |
| created_at | timestamp | Account creation time |
| updated_at | timestamp | Last update time |

### posts Table

| Column | Type | Description |
|--------|------|-------------|
| id | bigint (PK) | Auto-incrementing ID |
| user_id | bigint (FK) | Post author |
| content | text | Post content |
| image_path | string | Path to post image |
| visibility | string | public/private |
| created_at | timestamp | Creation time |
| updated_at | timestamp | Last update time |

### follows Table

| Column | Type | Description |
|--------|------|-------------|
| id | bigint (PK) | Auto-incrementing ID |
| follower_id | bigint (FK) | User who is following |
| following_id | bigint (FK) | User being followed |
| status | string | accepted/pending |
| created_at | timestamp | Follow creation time |
| updated_at | timestamp | Last update time |

## 🧪 Testing

The application includes comprehensive test coverage with 120+ test cases:

### Test Files

- `tests/Feature/User/ProfileTest.php` - 20+ tests for user profiles
- `tests/Feature/User/SettingTest.php` - 15+ tests for account settings
- `tests/Feature/User/PostTest.php` - 40+ tests for posts
- `tests/Feature/User/FollowTest.php` - 50+ tests for follow system
- `tests/Feature/User/UserPolicyTest.php` - 15+ tests for user policies

### Running Tests

```bash
php artisan test
```

All tests should pass if the application is properly configured.

## 🎨 Features in Detail

### Authentication

- **Secure Login**: Login throttling (5 attempts per minute) to prevent brute force attacks
- **Password Reset**: Full password reset flow with email tokens
- **Email Verification**: Users must verify email to access all features
- **Session Management**: Secure session handling with CSRF protection

### User Profiles

- **Custom URLs**: Profile accessible at `/@username`
- **Profile Information**: Display name, username, bio, location, website, birthdate
- **Photos**: Profile photo and cover photo upload with validation
- **Privacy**: Toggle between public and private account
- **Default Avatar**: Automatic fallback when no profile photo

### Account Settings

- **Update Information**: Change name and email
- **Change Password**: Secure password update with current password verification
- **Privacy Settings**: Control account visibility
- **Photo Management**: Upload and remove profile/cover photos

### Posts

- **Text Content**: Posts can contain up to 5000 characters
- **Images**: Optional image attachments (JPEG, PNG, GIF, WEBP, max 2MB)
- **Visibility**: Public or private posts
- **Timestamps**: Creation and update timestamps displayed
- **Author Information**: Shows author's name, username, and profile photo
- **Pagination**: Posts feed paginated with 10 items per page

### Follow System

- **Follow/Unfollow**: Follow or unfollow any user (except yourself)
- **Follow Requests**: Private accounts require follow request approval
- **Followers List**: Paginated list of users who follow you
- **Following List**: Paginated list of users you follow
- **Counts**: Follower and following counts displayed on profiles
- **Status**: Follow/Unfollow/Requested buttons based on current state

## 🔒 Security Features

### Authentication & Authorization

- **CSRF Protection**: All forms protected with CSRF tokens
- **Authentication Middleware**: Protected routes require login
- **Authorization Policies**: Fine-grained access control for all actions
- **Ownership Enforcement**: Users can only edit/delete their own content

### Data Validation

- **Form Validation**: Server-side validation for all inputs
- **Image Upload**: File type, size, and validity validation
- **Username Validation**: Unique, alpha_dash format
- **Email Validation**: Valid email format, unique, lowercase

### Privacy

- **Private Accounts**: Only accepted followers can view content
- **Public Accounts**: Content visible to everyone
- **Follow Requests**: Required for private accounts
- **Visibility Controls**: Users control who can see their content

### File Storage

- **Secure Uploads**: Images validated and stored securely
- **Path Validation**: Prevents directory traversal attacks
- **File Cleanup**: Automatic cleanup when files are replaced or deleted
- **Storage Configuration**: Configurable storage backend (local, S3, etc.)

## 📄 Documentation

The project includes comprehensive documentation:

- **README.md** - This file, general project information
- **IMPLEMENTATION_SUMMARY.md** - Authentication and profiles implementation details
- **POSTS_IMPLEMENTATION.md** - Posts module implementation details
- **FOLLOW_SYSTEM_IMPLEMENTATION.md** - Follow system implementation details
- **ROUTES_REFERENCE.md** - Complete list of all routes

## 🤝 Contributing

Contributions are welcome! Please follow these steps:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/your-feature`)
3. Commit your changes (`git commit -m 'Add some feature'`)
4. Push to the branch (`git push origin feature/your-feature`)
5. Open a Pull Request

## 📜 License

This project is open-source software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## 🙏 Acknowledgments

- [Laravel Framework](https://laravel.com/)
- [Tailwind CSS](https://tailwindcss.com/)
- [Vite](https://vitejs.dev/)
- [Pest PHP](https://pestphp.com/)

## 📞 Support

For questions or issues, please open a GitHub issue or contact the project maintainer.

---

**Built with ❤️ using Laravel**

*Last updated: 2026-10-11*
