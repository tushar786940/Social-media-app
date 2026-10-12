@extends('layouts.app')

@section('title', 'Create Post - ' . config('app.name'))

@section('content')
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    Create Post
                </h1>
                <p class="text-gray-600 dark:text-gray-400 mt-1">
                    Share your thoughts with the world
                </p>
            </div>
        </div>

        <!-- Create Post Form -->
        <form 
            method="POST" 
            action="{{ route('posts.store') }}" 
            enctype="multipart/form-data"
            class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-8"
            data-loading
        >
            @csrf

            <!-- Content -->
            <div class="mb-6">
                <x-forms.textarea 
                    name="content" 
                    label="What's on your mind?" 
                    placeholder="Share your thoughts..." 
                    rows="5"
                    required
                    :error="$errors->first('content')"
                    hint="Maximum 5000 characters"
                >{{ old('content') }}</x-forms.textarea>
            </div>

            <!-- Image Upload -->
            <div class="mb-6">
                <x-forms.file 
                    name="image" 
                    label="Add an image (optional)" 
                    accept="image/jpeg,image/png,image/gif,image/webp"
                    :error="$errors->first('image')"
                    hint="JPG, PNG, GIF, or WEBP. Max 2MB."
                />
            </div>

            <!-- Visibility -->
            <div class="mb-6">
                <x-forms.select 
                    name="visibility" 
                    label="Post Visibility" 
                    :options="[
                        ['value' => 'public', 'label' => 'Public - Anyone can see this post'],
                        ['value' => 'private', 'label' => 'Private - Only you can see this post'],
                    ]"
                    selected="{{ old('visibility', $defaultVisibility) }}"
                    :error="$errors->first('visibility')"
                    hint="Private posts are only visible to you"
                />
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end">
                <x-buttons.primary type="submit" size="lg">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Create Post
                </x-buttons.primary>
            </div>
        </form>
    </div>
@endsection
