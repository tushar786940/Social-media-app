<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['app.maintenance.driver' => 'file']);
});

describe('Login Throttling', function () {
    
    it('allows login within rate limit', function () {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        // First attempt should succeed
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password', // Wrong password to trigger error
        ]);

        $response->assertSessionHasErrors('email');
        
        // Second attempt should also work
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('email');
    });

    it('throttles login after too many attempts', function () {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        // Make multiple failed attempts
        for ($i = 0; $i < 5; $i++) {
            $response = $this->post('/login', [
                'email' => 'test@example.com',
                'password' => 'wrong-password',
            ]);
        }

        // Next attempt should be throttled
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123', // Even with correct password
        ]);

        $response->assertStatus(429);
    });

    it('throttling is per IP and email combination', function () {
        $user1 = User::factory()->create([
            'email' => 'user1@example.com',
            'password' => Hash::make('password123'),
        ]);
        
        $user2 = User::factory()->create([
            'email' => 'user2@example.com',
            'password' => Hash::make('password123'),
        ]);

        // Make multiple failed attempts for user1
        for ($i = 0; $i < 5; $i++) {
            $response = $this->post('/login', [
                'email' => 'user1@example.com',
                'password' => 'wrong-password',
            ]);
        }

        // user2 should still be able to login
        $response = $this->post('/login', [
            'email' => 'user2@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('home'));
    });
});
