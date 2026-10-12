<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\{
    LoginController,
    RegisterController,
    ForgotPasswordController,
    ResetPasswordController,
    VerifyEmailController,
};

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Public Routes
Route::middleware('guest')->group(function () {
    // Home page
    Route::get('/', function () {
        return view('pages.home');
    })->name('home');

    // Authentication Routes
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->middleware('throttle.login:5,1');
    
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
    
    // Password Reset Routes
    Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    
    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');
});

// Authenticated Routes
Route::middleware('auth')->group(function () {
    // Logout
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    
    // Email Verification Routes
    Route::get('/email/verify', [VerifyEmailController::class, 'showVerifyEmailNotice'])
        ->middleware('throttle:6,1')
        ->name('verification.notice');
    
    Route::get('/email/verify/{id}/{hash}', [VerifyEmailController::class, 'verify'])
        ->middleware('signed')
        ->name('verification.verify');
    
    Route::post('/email/verification-notification', [VerifyEmailController::class, 'sendVerificationEmail'])
        ->middleware('throttle:6,1')
        ->name('verification.send');
    
    // Profile Routes
    Route::get('/@{user:username}', [\App\Http\Controllers\User\ProfileController::class, 'show'])
        ->name('users.profile');
    
    Route::get('/profile/edit', [\App\Http\Controllers\User\ProfileController::class, 'edit'])
        ->name('profile.edit');
    
    Route::put('/profile', [\App\Http\Controllers\User\ProfileController::class, 'update'])
        ->name('profile.update');
    
    Route::post('/profile/photo', [\App\Http\Controllers\User\ProfileController::class, 'uploadProfilePhoto'])
        ->name('profile.photo.upload');
    
    Route::delete('/profile/photo', [\App\Http\Controllers\User\ProfileController::class, 'removeProfilePhoto'])
        ->name('profile.photo.remove');
    
    Route::delete('/profile/cover', [\App\Http\Controllers\User\ProfileController::class, 'removeCoverPhoto'])
        ->name('profile.cover.remove');
    
    // Settings Routes
    Route::get('/settings', [\App\Http\Controllers\User\SettingController::class, 'show'])
        ->name('settings');
    
    Route::put('/settings', [\App\Http\Controllers\User\SettingController::class, 'update'])
        ->name('settings.update');
    
    Route::put('/settings/password', [\App\Http\Controllers\User\SettingController::class, 'updatePassword'])
        ->name('settings.password');
    
    Route::put('/settings/privacy', [\App\Http\Controllers\User\SettingController::class, 'updatePrivacy'])
        ->name('settings.privacy');

    // Post Routes
    Route::get('/posts', [\App\Http\Controllers\User\PostController::class, 'index'])
        ->name('posts.index');
    
    Route::get('/posts/create', [\App\Http\Controllers\User\PostController::class, 'create'])
        ->name('posts.create');
    
    Route::post('/posts', [\App\Http\Controllers\User\PostController::class, 'store'])
        ->name('posts.store');
    
    Route::get('/posts/{post}', [\App\Http\Controllers\User\PostController::class, 'show'])
        ->name('posts.show');
    
    Route::get('/posts/{post}/edit', [\App\Http\Controllers\User\PostController::class, 'edit'])
        ->name('posts.edit');
    
    Route::put('/posts/{post}', [\App\Http\Controllers\User\PostController::class, 'update'])
        ->name('posts.update');
    
    Route::delete('/posts/{post}', [\App\Http\Controllers\User\PostController::class, 'destroy'])
        ->name('posts.destroy');
    
    Route::delete('/posts/{post}/image', [\App\Http\Controllers\User\PostController::class, 'removeImage'])
        ->name('posts.image.remove');
    
    // Follow Routes
    Route::post('/follow/{user}', [\App\Http\Controllers\User\FollowController::class, 'follow'])
        ->name('follow')
        ->middleware('auth');
    
    Route::delete('/follow/{user}', [\App\Http\Controllers\User\FollowController::class, 'unfollow'])
        ->name('unfollow')
        ->middleware('auth');
    
    Route::post('/follow/{user}/accept', [\App\Http\Controllers\User\FollowController::class, 'accept'])
        ->name('follow.accept')
        ->middleware('auth');
    
    Route::post('/follow/{user}/reject', [\App\Http\Controllers\User\FollowController::class, 'reject'])
        ->name('follow.reject')
        ->middleware('auth');
    
    Route::get('/@{user:username}/followers', [\App\Http\Controllers\User\FollowController::class, 'followers'])
        ->name('users.followers');
    
    Route::get('/@{user:username}/following', [\App\Http\Controllers\User\FollowController::class, 'following'])
        ->name('users.following');
    
    Route::get('/follow/status/{user}', [\App\Http\Controllers\User\FollowController::class, 'status'])
        ->name('follow.status')
        ->middleware('auth');

    // Like Routes
    Route::post('/posts/{post}/like', [\App\Http\Controllers\User\LikeController::class, 'store'])
        ->name('posts.like')
        ->middleware('auth');
    
    Route::delete('/posts/{post}/like', [\App\Http\Controllers\User\LikeController::class, 'destroy'])
        ->name('posts.unlike')
        ->middleware('auth');
    
    Route::post('/posts/{post}/like/toggle', [\App\Http\Controllers\User\LikeController::class, 'toggle'])
        ->name('posts.like.toggle')
        ->middleware('auth');

    // Comment Routes
    Route::get('/posts/{post}/comments', [\App\Http\Controllers\User\CommentController::class, 'index'])
        ->name('posts.comments.index');
    
    Route::post('/posts/{post}/comments', [\App\Http\Controllers\User\CommentController::class, 'store'])
        ->name('comments.store')
        ->middleware('auth');
    
    Route::put('/posts/{post}/comments/{comment}', [\App\Http\Controllers\User\CommentController::class, 'update'])
        ->name('comments.update')
        ->middleware('auth');
    
    Route::delete('/posts/{post}/comments/{comment}', [\App\Http\Controllers\User\CommentController::class, 'destroy'])
        ->name('comments.destroy')
        ->middleware('auth');

    // Feed Routes
    Route::get('/feed', [\App\Http\Controllers\User\FeedController::class, 'index'])
        ->name('feed')
        ->middleware('auth');
    
    Route::get('/discover', [\App\Http\Controllers\User\FeedController::class, 'discover'])
        ->name('discover');
});

// Search route
Route::get('/search', function () {
    return view('pages.home'); // Will be implemented later
})->name('search');

// Health check endpoint
Route::get('/health', function () {
    return response()->json(['status' => 'ok', 'timestamp' => now()]);
});

// Fallback route for undefined routes (handled by error pages)
Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});
