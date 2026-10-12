<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Ensure we're not in maintenance mode
    config(['app.maintenance.driver' => 'file']);
});

describe('Authentication', function () {
    
    describe('Login', function () {
        
        it('can view login page', function () {
            $response = $this->get('/login');
            
            $response->assertStatus(200);
            $response->assertViewIs('auth.login');
        });

        it('can login with valid credentials', function () {
            $user = User::factory()->create([
                'email' => 'test@example.com',
                'password' => Hash::make('password123'),
            ]);

            $response = $this->post('/login', [
                'email' => 'test@example.com',
                'password' => 'password123',
            ]);

            $response->assertRedirect(route('home'));
            $this->assertAuthenticatedAs($user);
        });

        it('cannot login with invalid credentials', function () {
            $user = User::factory()->create([
                'email' => 'test@example.com',
                'password' => Hash::make('password123'),
            ]);

            $response = $this->post('/login', [
                'email' => 'test@example.com',
                'password' => 'wrong-password',
            ]);

            $response->assertSessionHasErrors('email');
            $this->assertGuest();
        });

        it('cannot login with empty credentials', function () {
            $response = $this->post('/login', [
                'email' => '',
                'password' => '',
            ]);

            $response->assertSessionHasErrors(['email', 'password']);
            $this->assertGuest();
        });

        it('cannot login with invalid email format', function () {
            $response = $this->post('/login', [
                'email' => 'not-an-email',
                'password' => 'password123',
            ]);

            $response->assertSessionHasErrors('email');
            $this->assertGuest();
        });

        it('cannot access login page when authenticated', function () {
            $user = User::factory()->create();
            
            $response = $this->actingAs($user)->get('/login');
            
            $response->assertRedirect(route('home'));
        });

        it('regenerates session on login', function () {
            $user = User::factory()->create([
                'email' => 'test@example.com',
                'password' => Hash::make('password123'),
            ]);

            $oldSessionId = session()->getId();

            $response = $this->post('/login', [
                'email' => 'test@example.com',
                'password' => 'password123',
            ]);

            $this->assertSessionMissing('_previous_url');
            $this->assertNotEquals($oldSessionId, session()->getId());
        });
    });

    describe('Registration', function () {
        
        it('can view registration page', function () {
            $response = $this->get('/register');
            
            $response->assertStatus(200);
            $response->assertViewIs('auth.register');
        });

        it('can register with valid data', function () {
            $response = $this->post('/register', [
                'name' => 'Test User',
                'email' => 'newuser@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);

            $response->assertRedirect(route('home'));
            $this->assertAuthenticated();
            $this->assertDatabaseHas('users', [
                'email' => 'newuser@example.com',
                'name' => 'Test User',
            ]);
        });

        it('cannot register with empty data', function () {
            $response = $this->post('/register', [
                'name' => '',
                'email' => '',
                'password' => '',
                'password_confirmation' => '',
            ]);

            $response->assertSessionHasErrors(['name', 'email', 'password']);
            $this->assertGuest();
        });

        it('cannot register with invalid email', function () {
            $response = $this->post('/register', [
                'name' => 'Test User',
                'email' => 'not-an-email',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);

            $response->assertSessionHasErrors('email');
            $this->assertGuest();
        });

        it('cannot register with password mismatch', function () {
            $response = $this->post('/register', [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => 'password123',
                'password_confirmation' => 'different-password',
            ]);

            $response->assertSessionHasErrors('password');
            $this->assertGuest();
        });

        it('cannot register with short password', function () {
            $response = $this->post('/register', [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => 'short',
                'password_confirmation' => 'short',
            ]);

            $response->assertSessionHasErrors('password');
            $this->assertGuest();
        });

        it('cannot register with duplicate email', function () {
            User::factory()->create(['email' => 'duplicate@example.com']);

            $response = $this->post('/register', [
                'name' => 'Test User',
                'email' => 'duplicate@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);

            $response->assertSessionHasErrors('email');
            $this->assertGuest();
        });

        it('cannot access registration page when authenticated', function () {
            $user = User::factory()->create();
            
            $response = $this->actingAs($user)->get('/register');
            
            $response->assertRedirect(route('home'));
        });
    });

    describe('Logout', function () {
        
        it('can logout', function () {
            $user = User::factory()->create();
            
            $response = $this->actingAs($user)->post('/logout');
            
            $response->assertRedirect(route('home'));
            $this->assertGuest();
        });

        it('invalidates session on logout', function () {
            $user = User::factory()->create();
            
            $this->actingAs($user);
            $oldSessionId = session()->getId();
            
            $response = $this->post('/logout');
            
            $this->assertNotEquals($oldSessionId, session()->getId());
        });

        it('regenerates CSRF token on logout', function () {
            $user = User::factory()->create();
            
            $this->actingAs($user);
            $oldToken = csrf_token();
            
            $response = $this->post('/logout');
            
            $this->assertNotEquals($oldToken, csrf_token());
        });

        it('cannot logout when not authenticated', function () {
            $response = $this->post('/logout');
            
            $response->assertRedirect(route('login'));
            $this->assertGuest();
        });
    });

    describe('Password Reset', function () {
        
        it('can view forgot password page', function () {
            $response = $this->get('/forgot-password');
            
            $response->assertStatus(200);
            $response->assertViewIs('auth.passwords.email');
        });

        it('cannot access forgot password when authenticated', function () {
            $user = User::factory()->create();
            
            $response = $this->actingAs($user)->get('/forgot-password');
            
            $response->assertRedirect(route('home'));
        });
    });

    describe('Email Verification', function () {
        
        it('can view verification notice when email not verified', function () {
            $user = User::factory()->unverified()->create();
            
            $response = $this->actingAs($user)->get('/email/verify');
            
            $response->assertStatus(200);
            $response->assertViewIs('auth.verify-email');
        });

        it('cannot access verification page when email verified', function () {
            $user = User::factory()->create();
            
            $response = $this->actingAs($user)->get('/email/verify');
            
            $response->assertRedirect(route('home'));
        });
    });

    describe('Authorization', function () {
        
        it('redirects authenticated users from guest routes', function () {
            $user = User::factory()->create();
            
            $response = $this->actingAs($user)->get('/login');
            $response->assertRedirect(route('home'));
            
            $response = $this->actingAs($user)->get('/register');
            $response->assertRedirect(route('home'));
        });

        it('allows guest users to access guest routes', function () {
            $response = $this->get('/login');
            $response->assertStatus(200);
            
            $response = $this->get('/register');
            $response->assertStatus(200);
        });

        it('protects authenticated routes', function () {
            $response = $this->get('/email/verify');
            $response->assertRedirect(route('login'));
        });
    });

    describe('CSRF Protection', function () {
        
        it('requires CSRF token for login', function () {
            $user = User::factory()->create([
                'email' => 'test@example.com',
                'password' => Hash::make('password123'),
            ]);

            $response = $this->withCsrfToken('invalid')->post('/login', [
                'email' => 'test@example.com',
                'password' => 'password123',
            ]);

            $response->assertStatus(419);
        });

        it('requires CSRF token for registration', function () {
            $response = $this->withCsrfToken('invalid')->post('/register', [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);

            $response->assertStatus(419);
        });

        it('requires CSRF token for logout', function () {
            $user = User::factory()->create();
            
            $response = $this->actingAs($user)->withCsrfToken('invalid')->post('/logout');
            
            $response->assertStatus(419);
        });
    });
});
