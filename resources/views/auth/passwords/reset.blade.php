@extends('layouts.app')

@section('title', 'Reset Password - ' . config('app.name'))

@section('content')
    <div class="min-h-screen flex items-center justify-center px-4 py-12 sm:px-6 lg:px-8">
        <div class="w-full max-w-md space-y-8">
            <!-- Logo -->
            <div class="text-center">
                <a href="{{ route('home') }}" class="flex items-center justify-center space-x-2 mb-6 mx-auto">
                    <svg class="h-12 w-12 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/>
                    </svg>
                    <span class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ config('app.name', 'Social') }}
                    </span>
                </a>
                <h2 class="text-3xl font-bold text-gray-900 dark:text-white">
                    Reset Password
                </h2>
                <p class="mt-2 text-gray-600 dark:text-gray-400">
                    Set a new password for your account
                </p>
            </div>

            <!-- Reset Password Form -->
            <form method="POST" action="{{ route('password.update') }}" class="space-y-6" data-loading>
                @csrf

                <!-- Hidden Token -->
                <input type="hidden" name="token" value="{{ $token }}">

                <!-- Email -->
                <x-forms.input 
                    name="email" 
                    type="email" 
                    label="Email Address" 
                    placeholder="you@example.com" 
                    required
                    :error="$errors->first('email')"
                    value="{{ old('email', $email ?? '') }}"
                />

                <!-- Password -->
                <x-forms.input 
                    name="password" 
                    type="password" 
                    label="New Password" 
                    placeholder="Create a new strong password" 
                    required
                    :error="$errors->first('password')"
                    hint="Must be at least 8 characters"
                />

                <!-- Confirm Password -->
                <x-forms.input 
                    name="password_confirmation" 
                    type="password" 
                    label="Confirm New Password" 
                    placeholder="Confirm your new password" 
                    required
                    :error="$errors->first('password_confirmation')"
                />

                <!-- Submit Button -->
                <div>
                    <x-buttons.primary 
                        type="submit" 
                        full
                        size="lg"
                    >
                        Reset Password
                    </x-buttons.primary>
                </div>
            </form>

            <!-- Back to Login -->
            <div class="text-center text-sm text-gray-600 dark:text-gray-400">
                Remember your password?
                <a href="{{ route('login') }}" class="font-medium text-blue-600 dark:text-blue-400 hover:text-blue-500 dark:hover:text-blue-300">
                    Sign in
                </a>
            </div>
        </div>
    </div>
@endsection
