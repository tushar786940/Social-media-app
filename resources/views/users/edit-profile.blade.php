@extends('layouts.app')

@section('title', 'Edit Profile - ' . config('app.name'))

@section('content')
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    Edit Profile
                </h1>
                <p class="text-gray-600 dark:text-gray-400 mt-1">
                    Update your profile information
                </p>
            </div>
            
            <div class="flex items-center space-x-4">
                <a 
                    href="{{ route('users.profile', ['user' => $user->username]) }}" 
                    class="inline-flex items-center px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 text-sm font-medium transition-colors duration-200"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    View Profile
                </a>
                
                <x-buttons.secondary 
                    type="submit" 
                    form="profile-form"
                >
                    Save Changes
                </x-buttons.secondary>
            </div>
        </div>

        <!-- Profile Form -->
        <form 
            id="profile-form" 
            method="POST" 
            action="{{ route('profile.update') }}" 
            enctype="multipart/form-data"
            class="space-y-8"
            data-loading
        >
            @csrf
            @method('PUT')

            <!-- Profile Photo -->
            <div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    Profile Photo
                </h2>
                
                <div class="flex items-center space-x-6">
                    <div class="flex-shrink-0">
                        <img 
                            src="{{ $user->profile_photo }}" 
                            alt="{{ $user->displayName() }}" 
                            class="w-24 h-24 rounded-full object-cover"
                        >
                    </div>
                    
                    <div>
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Profile Photo
                        </p>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                            JPG, PNG, GIF, or WEBP. Max 2MB.
                        </p>
                        
                        <div class="flex space-x-3">
                            <x-forms.file 
                                name="profile_photo" 
                                label=""
                                accept="image/jpeg,image/png,image/gif,image/webp"
                                hint=""
                            />
                            
                            @if($user->profile_photo_path)
                                <form method="POST" action="{{ route('profile.photo.remove') }}" class="self-end">
                                    @csrf
                                    @method('DELETE')
                                    <x-buttons.danger type="submit" size="sm">
                                        Remove
                                    </x-buttons.danger>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cover Photo -->
            <div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    Cover Photo
                </h2>
                
                    <div class="mb-4">
                        @if($user->cover_photo)
                            <img 
                                src="{{ $user->cover_photo }}" 
                                alt="Cover photo" 
                                class="w-full h-32 object-cover rounded-lg mb-4"
                            >
                        @else
                            <div class="w-full h-32 bg-gray-100 dark:bg-gray-800 rounded-lg mb-4 flex items-center justify-center">
                                <p class="text-gray-400 dark:text-gray-500 text-sm">
                                    No cover photo
                                </p>
                            </div>
                        @endif
                        
                        <div class="flex space-x-3">
                            <x-forms.file 
                                name="cover_photo" 
                                label=""
                                accept="image/jpeg,image/png,image/gif,image/webp"
                                hint=""
                            />
                            
                            @if($user->cover_photo_path)
                                <form method="POST" action="{{ route('profile.cover.remove') }}" class="self-end">
                                    @csrf
                                    @method('DELETE')
                                    <x-buttons.danger type="submit" size="sm">
                                        Remove
                                    </x-buttons.danger>
                                </form>
                            @endif
                        </div>
                        
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                            JPG, PNG, GIF, or WEBP. Max 2MB. Recommended: 1200x400 pixels.
                        </p>
                    </div>
            </div>

            <!-- Profile Information -->
            <div class="border-t border-gray-200 dark:border-gray-700 pt-8">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">
                    Profile Information
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Name -->
                    <x-forms.input 
                        name="name" 
                        type="text" 
                        label="Display Name" 
                        placeholder="Your display name" 
                        required
                        :error="$errors->first('name')"
                        value="{{ old('name', $user->name) }}"
                    />
                    
                    <!-- Username -->
                    <x-forms.input 
                        name="username" 
                        type="text" 
                        label="Username" 
                        placeholder="your-username" 
                        required
                        :error="$errors->first('username')"
                        value="{{ old('username', $user->username) }}"
                        hint="Only letters, numbers, and hyphens. Must be unique."
                    />
                    
                    <!-- Bio -->
                    <x-forms.textarea 
                        name="bio" 
                        label="Bio" 
                        placeholder="Tell the world about yourself" 
                        rows="4"
                        :error="$errors->first('bio')"
                        hint="Maximum 500 characters"
                    >{{ old('bio', $user->bio) }}</x-forms.textarea>
                    
                    <!-- Location -->
                    <x-forms.input 
                        name="location" 
                        type="text" 
                        label="Location" 
                        placeholder="Where are you?" 
                        :error="$errors->first('location')"
                        value="{{ old('location', $user->location) }}"
                    />
                    
                    <!-- Website -->
                    <x-forms.input 
                        name="website" 
                        type="url" 
                        label="Website" 
                        placeholder="https://your-website.com" 
                        :error="$errors->first('website')"
                        value="{{ old('website', $user->website) }}"
                    />
                    
                    <!-- Birthdate -->
                    <x-forms.input 
                        name="birthdate" 
                        type="date" 
                        label="Birthdate" 
                        :error="$errors->first('birthdate')"
                        value="{{ old('birthdate', $user->birthdate ? $user->birthdate->format('Y-m-d') : '') }}"
                    />
                </div>
            </div>

            <!-- Privacy Settings -->
            <div class="border-t border-gray-200 dark:border-gray-700 pt-8">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">
                    Privacy Settings
                </h2>
                
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                Private Account
                            </h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Only approved followers can see your posts
                            </p>
                        </div>
                        
                        <x-forms.checkbox 
                            name="is_private" 
                            :checked="old('is_private', $user->is_private)"
                        />
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="border-t border-gray-200 dark:border-gray-700 pt-8">
                <div class="flex justify-end">
                    <x-buttons.primary 
                        type="submit" 
                        size="lg"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Save Changes
                    </x-buttons.primary>
                </div>
            </div>
        </form>
    </div>
@endsection
