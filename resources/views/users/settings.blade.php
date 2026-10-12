@extends('layouts.app')

@section('title', 'Account Settings - ' . config('app.name'))

@section('content')
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    Account Settings
                </h1>
                <p class="text-gray-600 dark:text-gray-400 mt-1">
                    Manage your account preferences
                </p>
            </div>
        </div>

        <!-- Settings Navigation -->
        <div class="border-b border-gray-200 dark:border-gray-700 mb-8">
            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                <a 
                    href="#profile" 
                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm {{ request()->is('settings') || request()->is('settings/profile') ? 'border-blue-500 text-blue-600 dark:border-blue-400 dark:text-blue-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600' }}"
                >
                    Profile
                </a>
                <a 
                    href="#password" 
                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm {{ request()->is('settings/password') ? 'border-blue-500 text-blue-600 dark:border-blue-400 dark:text-blue-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600' }}"
                >
                    Password
                </a>
            </nav>
        </div>

        <!-- Profile Settings -->
        <div id="profile" class="mb-8">
            <form 
                method="POST" 
                action="{{ route('settings.update') }}" 
                class="space-y-6"
                data-loading
            >
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Name -->
                    <x-forms.input 
                        name="name" 
                        type="text" 
                        label="Full Name" 
                        placeholder="Your full name" 
                        required
                        :error="$errors->first('name')"
                        value="{{ old('name', $user->name) }}"
                    />
                    
                    <!-- Email -->
                    <x-forms.input 
                        name="email" 
                        type="email" 
                        label="Email Address" 
                        placeholder="you@example.com" 
                        required
                        :error="$errors->first('email')"
                        value="{{ old('email', $user->email) }}"
                        hint="Email must be verified to use all features"
                    />
                </div>

                <div class="flex justify-end">
                    <x-buttons.primary type="submit">
                        Save Profile Settings
                    </x-buttons.primary>
                </div>
            </form>
        </div>

        <!-- Password Settings -->
        <div id="password" class="mb-8">
            <div class="bg-yellow-50 dark:bg-yellow-900/20 rounded-lg p-4 border border-yellow-200 dark:border-yellow-800 mb-6">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <p class="text-yellow-800 dark:text-yellow-200 text-sm">
                        Changing your password will log you out of all other devices.
                    </p>
                </div>
            </div>

            <form 
                method="POST" 
                action="{{ route('settings.password') }}" 
                class="space-y-6"
                data-loading
            >
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 gap-6">
                    <!-- Current Password -->
                    <x-forms.input 
                        name="current_password" 
                        type="password" 
                        label="Current Password" 
                        placeholder="Enter your current password" 
                        required
                        :error="$errors->first('current_password')"
                    />
                    
                    <!-- New Password -->
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
                </div>

                <div class="flex justify-end">
                    <x-buttons.primary type="submit">
                        Change Password
                    </x-buttons.primary>
                </div>
            </form>
        </div>

        <!-- Danger Zone -->
        <div class="border-t border-gray-200 dark:border-gray-700 pt-8">
            <h2 class="text-lg font-semibold text-red-600 dark:text-red-400 mb-4">
                Danger Zone
            </h2>
            
            <div class="bg-red-50 dark:bg-red-900/20 rounded-lg p-4 border border-red-200 dark:border-red-800">
                <h3 class="text-sm font-medium text-red-800 dark:text-red-200 mb-2">
                    Delete Account
                </h3>
                <p class="text-sm text-red-700 dark:text-red-300 mb-4">
                    Once you delete your account, there is no going back. Please be certain.
                </p>
                <x-buttons.danger size="sm">
                    Delete My Account
                </x-buttons.danger>
            </div>
        </div>
    </div>
@endsection
