<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['app.maintenance.driver' => 'file']);
});

describe('User Settings', function () {
    
    describe('Settings Page', function () {
        
        it('can view settings page when authenticated', function () {
            $user = User::factory()->create();

            $response = $this->actingAs($user)
                ->get('/settings');

            $response->assertStatus(200);
            $response->assertViewIs('users.settings');
            $response->assertViewHas('user', $user);
        });

        it('cannot view settings page when not authenticated', function () {
            $response = $this->get('/settings');

            $response->assertRedirect('/login');
        });
    });

    describe('Update Settings', function () {
        
        it('can update name and email', function () {
            $user = User::factory()->create([
                'name' => 'Old Name',
                'email' => 'old@example.com',
            ]);

            $response = $this->actingAs($user)
                ->put('/settings', [
                    'name' => 'New Name',
                    'email' => 'new@example.com',
                ]);

            $response->assertRedirect();
            
            $user->refresh();
            
            expect($user->name)->toBe('New Name');
            expect($user->email)->toBe('new@example.com');
        });

        it('validates name field', function () {
            $user = User::factory()->create();

            $response = $this->actingAs($user)
                ->put('/settings', [
                    'name' => '',
                    'email' => $user->email,
                ]);

            $response->assertSessionHasErrors('name');
        });

        it('validates email field', function () {
            $user = User::factory()->create();

            $response = $this->actingAs($user)
                ->put('/settings', [
                    'name' => $user->name,
                    'email' => 'not-an-email',
                ]);

            $response->assertSessionHasErrors('email');
        });

        it('validates email uniqueness', function () {
            $user1 = User::factory()->create(['email' => 'taken@example.com']);
            $user2 = User::factory()->create();

            $response = $this->actingAs($user2)
                ->put('/settings', [
                    'name' => $user2->name,
                    'email' => 'taken@example.com',
                ]);

            $response->assertSessionHasErrors('email');
        });

        it('cannot update another users settings', function () {
            $user1 = User::factory()->create();
            $user2 = User::factory()->create();

            $response = $this->actingAs($user1)
                ->put('/settings', [
                    'name' => 'Hacker',
                    'email' => 'hacker@example.com',
                ]);

            $response->assertRedirect();
            
            $user2->refresh();
            expect($user2->name)->not->toBe('Hacker');
            expect($user2->email)->not->toBe('hacker@example.com');
        });
    });

    describe('Change Password', function () {
        
        it('can change password with correct current password', function () {
            $user = User::factory()->create([
                'password' => Hash::make('oldpassword'),
            ]);

            $response = $this->actingAs($user)
                ->put('/settings/password', [
                    'current_password' => 'oldpassword',
                    'password' => 'newpassword123',
                    'password_confirmation' => 'newpassword123',
                ]);

            $response->assertRedirect();
            
            $user->refresh();
            
            expect(Hash::check('newpassword123', $user->password))->toBeTrue();
            expect(Hash::check('oldpassword', $user->password))->toBeFalse();
        });

        it('cannot change password with incorrect current password', function () {
            $user = User::factory()->create([
                'password' => Hash::make('oldpassword'),
            ]);

            $response = $this->actingAs($user)
                ->put('/settings/password', [
                    'current_password' => 'wrongpassword',
                    'password' => 'newpassword123',
                    'password_confirmation' => 'newpassword123',
                ]);

            $response->assertSessionHasErrors('current_password');
            
            $user->refresh();
            expect(Hash::check('oldpassword', $user->password))->toBeTrue();
        });

        it('validates new password strength', function () {
            $user = User::factory()->create([
                'password' => Hash::make('oldpassword'),
            ]);

            $response = $this->actingAs($user)
                ->put('/settings/password', [
                    'current_password' => 'oldpassword',
                    'password' => 'short',
                    'password_confirmation' => 'short',
                ]);

            $response->assertSessionHasErrors('password');
        });

        it('validates password confirmation', function () {
            $user = User::factory()->create([
                'password' => Hash::make('oldpassword'),
            ]);

            $response = $this->actingAs($user)
                ->put('/settings/password', [
                    'current_password' => 'oldpassword',
                    'password' => 'newpassword123',
                    'password_confirmation' => 'different-password',
                ]);

            $response->assertSessionHasErrors('password');
        });

        it('requires current password', function () {
            $user = User::factory()->create([
                'password' => Hash::make('oldpassword'),
            ]);

            $response = $this->actingAs($user)
                ->put('/settings/password', [
                    'current_password' => '',
                    'password' => 'newpassword123',
                    'password_confirmation' => 'newpassword123',
                ]);

            $response->assertSessionHasErrors('current_password');
        });

        it('requires new password', function () {
            $user = User::factory()->create([
                'password' => Hash::make('oldpassword'),
            ]);

            $response = $this->actingAs($user)
                ->put('/settings/password', [
                    'current_password' => 'oldpassword',
                    'password' => '',
                    'password_confirmation' => '',
                ]);

            $response->assertSessionHasErrors('password');
        });

        it('cannot change another users password', function () {
            $user1 = User::factory()->create([
                'password' => Hash::make('password1'),
            ]);
            $user2 = User::factory()->create([
                'password' => Hash::make('password2'),
            ]);

            $response = $this->actingAs($user1)
                ->put('/settings/password', [
                    'current_password' => 'password1',
                    'password' => 'newpassword',
                    'password_confirmation' => 'newpassword',
                ]);

            $response->assertRedirect();
            
            $user2->refresh();
            expect(Hash::check('password2', $user2->password))->toBeTrue();
            expect(Hash::check('newpassword', $user2->password))->toBeFalse();
        });
    });

    describe('Privacy Settings', function () {
        
        it('can update privacy settings', function () {
            $user = User::factory()->create([
                'is_private' => false,
            ]);

            $response = $this->actingAs($user)
                ->put('/settings/privacy', [
                    'is_private' => true,
                ]);

            $response->assertRedirect();
            
            $user->refresh();
            
            expect($user->is_private)->toBeTrue();
        });

        it('validates privacy setting is boolean', function () {
            $user = User::factory()->create();

            $response = $this->actingAs($user)
                ->put('/settings/privacy', [
                    'is_private' => 'not-a-boolean',
                ]);

            $response->assertSessionHasErrors('is_private');
        });

        it('requires privacy setting', function () {
            $user = User::factory()->create();

            $response = $this->actingAs($user)
                ->put('/settings/privacy', []);

            $response->assertSessionHasErrors('is_private');
        });

        it('cannot update another users privacy settings', function () {
            $user1 = User::factory()->create([
                'is_private' => false,
            ]);
            $user2 = User::factory()->create([
                'is_private' => false,
            ]);

            $response = $this->actingAs($user1)
                ->put('/settings/privacy', [
                    'is_private' => true,
                ]);

            $response->assertRedirect();
            
            $user2->refresh();
            expect($user2->is_private)->toBeFalse();
        });
    });

    describe('Display Name', function () {
        
        it('returns name as display name when available', function () {
            $user = User::factory()->create([
                'name' => 'John Doe',
                'username' => 'johndoe',
            ]);

            expect($user->displayName())->toBe('John Doe');
        });

        it('returns username as display name when name is null', function () {
            $user = User::factory()->create([
                'name' => null,
                'username' => 'johndoe',
            ]);

            expect($user->displayName())->toBe('johndoe');
        });
    });

    describe('Formatted Birthdate', function () {
        
        it('returns formatted birthdate when available', function () {
            $user = User::factory()->create([
                'birthdate' => '1990-01-15',
            ]);

            expect($user->formattedBirthdate())->toBe('January 15, 1990');
        });

        it('returns null when birthdate is not set', function () {
            $user = User::factory()->create([
                'birthdate' => null,
            ]);

            expect($user->formattedBirthdate())->toBeNull();
        });
    });

    describe('Age Calculation', function () {
        
        it('returns age when birthdate is available', function () {
            $user = User::factory()->create([
                'birthdate' => now()->subYears(25)->format('Y-m-d'),
            ]);

            expect($user->age())->toBe(25);
        });

        it('returns null when birthdate is not set', function () {
            $user = User::factory()->create([
                'birthdate' => null,
            ]);

            expect($user->age())->toBeNull();
        });
    });
});
