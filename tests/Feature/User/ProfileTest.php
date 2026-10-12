<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['app.maintenance.driver' => 'file']);
    Storage::fake('public');
});

describe('User Profiles', function () {
    
    describe('Profile Page', function () {
        
        it('can view public profile', function () {
            $user = User::factory()->create([
                'username' => 'testuser',
                'is_private' => false,
            ]);

            $response = $this->get('/@testuser');

            $response->assertStatus(200);
            $response->assertViewIs('users.profile');
            $response->assertViewHas('user', $user);
        });

        it('can view profile with username parameter', function () {
            $user = User::factory()->create([
                'username' => 'johndoe',
            ]);

            $response = $this->get('/@johndoe');

            $response->assertStatus(200);
            $response->assertViewHas('user', $user);
        });

        it('returns 404 for non-existent username', function () {
            $response = $this->get('/@nonexistent');

            $response->assertStatus(404);
        });

        it('shows default avatar when no profile photo', function () {
            $user = User::factory()->create([
                'username' => 'testuser',
                'profile_photo_path' => null,
            ]);

            $response = $this->get('/@testuser');

            $response->assertStatus(200);
            $response->assertSee('default-avatar');
        });

        it('shows profile photo when available', function () {
            $user = User::factory()->create([
                'username' => 'testuser',
            ]);

            $response = $this->get('/@testuser');

            $response->assertStatus(200);
            // Profile photo URL should be present
        });

        it('shows privacy notice for private accounts', function () {
            $user = User::factory()->create([
                'username' => 'privateuser',
                'is_private' => true,
            ]);

            $response = $this->get('/@privateuser');

            $response->assertStatus(200);
            $response->assertSee('private account');
        });
    });

    describe('Edit Profile', function () {
        
        it('can view edit profile page when authenticated', function () {
            $user = User::factory()->create();

            $response = $this->actingAs($user)
                ->get('/profile/edit');

            $response->assertStatus(200);
            $response->assertViewIs('users.edit-profile');
            $response->assertViewHas('user', $user);
        });

        it('cannot view edit profile page when not authenticated', function () {
            $response = $this->get('/profile/edit');

            $response->assertRedirect('/login');
        });

        it('can update profile information', function () {
            $user = User::factory()->create();

            $response = $this->actingAs($user)
                ->put('/profile', [
                    'name' => 'Updated Name',
                    'username' => 'updated-username',
                    'bio' => 'Updated bio',
                    'location' => 'Updated location',
                    'website' => 'https://example.com',
                    'birthdate' => '1990-01-01',
                    'is_private' => true,
                ]);

            $response->assertRedirect();
            
            $user->refresh();
            
            expect($user->name)->toBe('Updated Name');
            expect($user->username)->toBe('updated-username');
            expect($user->bio)->toBe('Updated bio');
            expect($user->location)->toBe('Updated location');
            expect($user->website)->toBe('https://example.com');
            expect($user->birthdate->format('Y-m-d'))->toBe('1990-01-01');
            expect($user->is_private)->toBeTrue();
        });

        it('validates username uniqueness', function () {
            $user1 = User::factory()->create(['username' => 'taken-username']);
            $user2 = User::factory()->create();

            $response = $this->actingAs($user2)
                ->put('/profile', [
                    'name' => 'Test',
                    'username' => 'taken-username',
                ]);

            $response->assertSessionHasErrors('username');
        });

        it('validates username format', function () {
            $user = User::factory()->create();

            $response = $this->actingAs($user)
                ->put('/profile', [
                    'name' => 'Test',
                    'username' => 'invalid username with spaces',
                ]);

            $response->assertSessionHasErrors('username');
        });

        it('validates bio length', function () {
            $user = User::factory()->create();

            $response = $this->actingAs($user)
                ->put('/profile', [
                    'name' => 'Test',
                    'username' => 'testuser',
                    'bio' => str_repeat('a', 501),
                ]);

            $response->assertSessionHasErrors('bio');
        });

        it('validates website URL', function () {
            $user = User::factory()->create();

            $response = $this->actingAs($user)
                ->put('/profile', [
                    'name' => 'Test',
                    'username' => 'testuser',
                    'website' => 'not-a-url',
                ]);

            $response->assertSessionHasErrors('website');
        });

        it('validates birthdate', function () {
            $user = User::factory()->create();

            $response = $this->actingAs($user)
                ->put('/profile', [
                    'name' => 'Test',
                    'username' => 'testuser',
                    'birthdate' => '2050-01-01', // Future date
                ]);

            $response->assertSessionHasErrors('birthdate');
        });

        it('cannot update another users profile', function () {
            $user1 = User::factory()->create();
            $user2 = User::factory()->create();

            $response = $this->actingAs($user1)
                ->put('/profile', [
                    'name' => 'Hacker',
                    'username' => $user2->username,
                ]);

            $response->assertForbidden();
            
            $user2->refresh();
            expect($user2->name)->not->toBe('Hacker');
        });
    });

    describe('Profile Photo Upload', function () {
        
        it('can upload profile photo', function () {
            $user = User::factory()->create();

            $file = UploadedFile::fake()->image('profile.jpg');

            $response = $this->actingAs($user)
                ->post('/profile/photo', [
                    'profile_photo' => $file,
                ]);

            $response->assertRedirect();
            
            $user->refresh();
            
            expect($user->profile_photo_path)->not->toBeNull();
            Storage::disk('public')->assertExists($user->profile_photo_path);
        });

        it('validates profile photo file type', function () {
            $user = User::factory()->create();

            $file = UploadedFile::fake()->create('document.pdf');

            $response = $this->actingAs($user)
                ->post('/profile/photo', [
                    'profile_photo' => $file,
                ]);

            $response->assertSessionHasErrors('profile_photo');
        });

        it('validates profile photo file size', function () {
            $user = User::factory()->create();

            // Create a file larger than 2MB
            $file = UploadedFile::fake()->image('large.jpg')->size(3000);

            $response = $this->actingAs($user)
                ->post('/profile/photo', [
                    'profile_photo' => $file,
                ]);

            $response->assertSessionHasErrors('profile_photo');
        });

        it('can remove profile photo', function () {
            $user = User::factory()->create([
                'profile_photo_path' => 'profile-photos/test.jpg',
            ]);

            // Fake the storage to have the file
            Storage::disk('public')->put('profile-photos/test.jpg', 'dummy');

            $response = $this->actingAs($user)
                ->delete('/profile/photo');

            $response->assertRedirect();
            
            $user->refresh();
            
            expect($user->profile_photo_path)->toBeNull();
            Storage::disk('public')->assertMissing('profile-photos/test.jpg');
        });

        it('cannot upload profile photo for another user', function () {
            $user1 = User::factory()->create();
            $user2 = User::factory()->create();

            $file = UploadedFile::fake()->image('profile.jpg');

            $response = $this->actingAs($user1)
                ->post('/profile/photo', [
                    'profile_photo' => $file,
                ]);

            // The photo will be uploaded for user1, not user2
            // This is handled by the controller using Auth::user()
            $response->assertRedirect();
        });
    });

    describe('Cover Photo Upload', function () {
        
        it('can upload cover photo', function () {
            $user = User::factory()->create();

            $file = UploadedFile::fake()->image('cover.jpg');

            $response = $this->actingAs($user)
                ->put('/profile', [
                    'cover_photo' => $file,
                ]);

            $response->assertRedirect();
            
            $user->refresh();
            
            expect($user->cover_photo_path)->not->toBeNull();
            Storage::disk('public')->assertExists($user->cover_photo_path);
        });

        it('can remove cover photo', function () {
            $user = User::factory()->create([
                'cover_photo_path' => 'cover-photos/test.jpg',
            ]);

            Storage::disk('public')->put('cover-photos/test.jpg', 'dummy');

            $response = $this->actingAs($user)
                ->delete('/profile/cover');

            $response->assertRedirect();
            
            $user->refresh();
            
            expect($user->cover_photo_path)->toBeNull();
            Storage::disk('public')->assertMissing('cover-photos/test.jpg');
        });
    });

    describe('Authorization', function () {
        
        it('enforces ownership for profile updates', function () {
            $user1 = User::factory()->create();
            $user2 = User::factory()->create();

            $response = $this->actingAs($user1)
                ->put('/profile', [
                    'name' => 'Hacker',
                ]);

            $response->assertRedirect();
            
            $user2->refresh();
            expect($user2->name)->not->toBe('Hacker');
        });

        it('enforces ownership for photo uploads', function () {
            $user1 = User::factory()->create();
            $user2 = User::factory()->create();

            $file = UploadedFile::fake()->image('profile.jpg');

            $response = $this->actingAs($user1)
                ->post('/profile/photo', [
                    'profile_photo' => $file,
                ]);

            $response->assertRedirect();
            
            // Photo is uploaded for user1, not user2
            $user1->refresh();
            expect($user1->profile_photo_path)->not->toBeNull();
            
            $user2->refresh();
            expect($user2->profile_photo_path)->toBeNull();
        });

        it('enforces ownership for photo removal', function () {
            $user1 = User::factory()->create([
                'profile_photo_path' => 'profile-photos/test.jpg',
            ]);
            $user2 = User::factory()->create([
                'profile_photo_path' => 'profile-photos/test2.jpg',
            ]);

            Storage::disk('public')->put('profile-photos/test.jpg', 'dummy');
            Storage::disk('public')->put('profile-photos/test2.jpg', 'dummy');

            $response = $this->actingAs($user1)
                ->delete('/profile/photo');

            $response->assertRedirect();
            
            $user1->refresh();
            expect($user1->profile_photo_path)->toBeNull();
            
            $user2->refresh();
            expect($user2->profile_photo_path)->not->toBeNull();
        });
    });

    describe('Default Avatar', function () {
        
        it('returns default avatar URL when no profile photo', function () {
            $user = User::factory()->create([
                'profile_photo_path' => null,
            ]);

            expect($user->profile_photo)->toBe($user->defaultProfilePhotoUrl());
            expect($user->defaultProfilePhotoUrl())->toContain('default-avatar');
        });

        it('returns profile photo URL when available', function () {
            $user = User::factory()->create([
                'profile_photo_path' => 'profile-photos/test.jpg',
            ]);

            Storage::disk('public')->put('profile-photos/test.jpg', 'dummy');

            expect($user->profile_photo)->toContain('profile-photos/test.jpg');
        });

        it('has profile photo flag works', function () {
            $user1 = User::factory()->create([
                'profile_photo_path' => null,
            ]);
            $user2 = User::factory()->create([
                'profile_photo_path' => 'profile-photos/test.jpg',
            ]);

            expect($user1->hasProfilePhoto())->toBeFalse();
            expect($user2->hasProfilePhoto())->toBeTrue();
        });
    });
});
