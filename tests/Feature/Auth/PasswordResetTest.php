<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['app.maintenance.driver' => 'file']);
});

describe('Password Reset', function () {
    
    it('can view password reset request page', function () {
        $response = $this->get('/forgot-password');
        
        $response->assertStatus(200);
        $response->assertViewIs('auth.passwords.email');
    });

    it('requires email for password reset request', function () {
        $response = $this->post('/forgot-password', [
            'email' => '',
        ]);
        
        $response->assertSessionHasErrors('email');
    });

    it('requires valid email format for password reset request', function () {
        $response = $this->post('/forgot-password', [
            'email' => 'not-an-email',
        ]);
        
        $response->assertSessionHasErrors('email');
    });

    it('shows success message when password reset link sent', function () {
        // Create a user
        User::factory()->create(['email' => 'test@example.com']);

        // Mock the password broker
        $this->mock(\Illuminate\Auth\Passwords\PasswordBroker::class, function ($mock) {
            $mock->shouldReceive('sendResetLink')
                ->andReturn(\Illuminate\Auth\Passwords\PasswordBroker::RESET_LINK_SENT);
        });

        $response = $this->post('/forgot-password', [
            'email' => 'test@example.com',
        ]);

        $response->assertRedirect('/forgot-password');
        $response->assertSessionHas('status');
    });

    it('can view password reset form with valid token', function () {
        $user = User::factory()->create();
        
        // Create a password reset token
        DB::table('password_reset_tokens')->insert([
            'email' => $user->email,
            'token' => Hash::make('test-token'),
            'created_at' => Carbon::now(),
        ]);

        $response = $this->get('/reset-password/test-token?email=' . urlencode($user->email));
        
        $response->assertStatus(200);
        $response->assertViewIs('auth.passwords.reset');
    });

    it('requires token for password reset', function () {
        $response = $this->post('/reset-password', [
            'token' => '',
            'email' => 'test@example.com',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);
        
        $response->assertSessionHasErrors('token');
    });

    it('requires email for password reset', function () {
        $response = $this->post('/reset-password', [
            'token' => 'test-token',
            'email' => '',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);
        
        $response->assertSessionHasErrors('email');
    });

    it('requires password for password reset', function () {
        $response = $this->post('/reset-password', [
            'token' => 'test-token',
            'email' => 'test@example.com',
            'password' => '',
            'password_confirmation' => '',
        ]);
        
        $response->assertSessionHasErrors('password');
    });

    it('requires password confirmation for password reset', function () {
        $response = $this->post('/reset-password', [
            'token' => 'test-token',
            'email' => 'test@example.com',
            'password' => 'newpassword123',
            'password_confirmation' => '',
        ]);
        
        $response->assertSessionHasErrors('password');
    });

    it('requires matching passwords for password reset', function () {
        $response = $this->post('/reset-password', [
            'token' => 'test-token',
            'email' => 'test@example.com',
            'password' => 'newpassword123',
            'password_confirmation' => 'different-password',
        ]);
        
        $response->assertSessionHasErrors('password');
    });

    it('requires strong password for password reset', function () {
        $response = $this->post('/reset-password', [
            'token' => 'test-token',
            'email' => 'test@example.com',
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);
        
        $response->assertSessionHasErrors('password');
    });
});
