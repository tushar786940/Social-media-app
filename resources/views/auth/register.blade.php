@extends('layouts.app')

@section('title', 'Register - ' . config('app.name'))

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
                    Create your account
                </h2>
                <p class="mt-2 text-gray-600 dark:text-gray-400">
                    Already have an account?
                    <a href="{{ route('login') }}" class="font-medium text-blue-600 dark:text-blue-400 hover:text-blue-500 dark:hover:text-blue-300">
                        Sign in
                    </a>
                </p>
            </div>

            <!-- Registration Form -->
            <form method="POST" action="{{ route('register') }}" class="space-y-6" data-loading>
                @csrf

                <!-- Name -->
                <x-forms.input 
                    name="name" 
                    type="text" 
                    label="Full Name" 
                    placeholder="John Doe" 
                    required
                    :error="$errors->first('name')"
                    value="{{ old('name') }}"
                />

                <!-- Email -->
                <x-forms.input 
                    name="email" 
                    type="email" 
                    label="Email Address" 
                    placeholder="you@example.com" 
                    required
                    :error="$errors->first('email')"
                    value="{{ old('email') }}"
                />

                <!-- Password -->
                <x-forms.input 
                    name="password" 
                    type="password" 
                    label="Password" 
                    placeholder="Create a strong password" 
                    required
                    :error="$errors->first('password')"
                    hint="Must be at least 8 characters"
                />

                <!-- Confirm Password -->
                <x-forms.input 
                    name="password_confirmation" 
                    type="password" 
                    label="Confirm Password" 
                    placeholder="Confirm your password" 
                    required
                    :error="$errors->first('password_confirmation')"
                />

                <!-- Terms -->
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <x-forms.checkbox 
                            name="terms" 
                            required
                        />
                    </div>
                    <div class="ml-2">
                        <label for="terms" class="text-sm text-gray-600 dark:text-gray-400">
                            I agree to the
                            <a href="#" class="font-medium text-blue-600 dark:text-blue-400 hover:text-blue-500 dark:hover:text-blue-300">
                                Terms of Service
                            </a>
                            and
                            <a href="#" class="font-medium text-blue-600 dark:text-blue-400 hover:text-blue-500 dark:hover:text-blue-300">
                                Privacy Policy
                            </a>
                        </label>
                    </div>
                </div>

                <!-- Submit Button -->
                <div>
                    <x-buttons.primary 
                        type="submit" 
                        full
                        size="lg"
                    >
                        Create Account
                    </x-buttons.primary>
                </div>
            </form>

            <!-- Social Login Placeholder -->
            <div class="relative mt-8">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-300 dark:border-gray-600"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-2 bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400">
                        Or sign up with
                    </span>
                </div>
            </div>

            <!-- Social Buttons -->
            <div class="mt-6 grid grid-cols-2 gap-3">
                <button 
                    type="button" 
                    class="w-full inline-flex justify-center items-center px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-medium hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200"
                >
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                    </svg>
                    GitHub
                </button>
                <button 
                    type="button" 
                    class="w-full inline-flex justify-center items-center px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-medium hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200"
                >
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/>
                    </svg>
                    Google
                </button>
            </div>
        </div>
    </div>
@endsection
