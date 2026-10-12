@extends('layouts.app')

@section('title', 'Edit Post - ' . config('app.name'))

@section('content')
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    Edit Post
                </h1>
                <p class="text-gray-600 dark:text-gray-400 mt-1">
                    Update your post content
                </p>
            </div>
        </div>

        <!-- Edit Post Form -->
        <form 
            method="POST" 
            action="{{ route('posts.update', ['post' => $post->id]) }}" 
            enctype="multipart/form-data"
            class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-8"
            data-loading
        >
            @csrf
            @method('PUT')

            <!-- Content -->
            <div class="mb-6">
                <x-forms.textarea 
                    name="content" 
                    label="Content" 
                    placeholder="Share your thoughts..." 
                    rows="5"
                    required
                    :error="$errors->first('content')"
                    hint="Maximum 5000 characters"
                >{{ old('content', $post->content) }}</x-forms.textarea>
            </div>

            <!-- Current Image -->
            @if($post->hasImage())
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Current Image
                    </label>
                    <div class="relative">
                        <img 
                            src="{{ $post->image_url }}" 
                            alt="Current post image" 
                            class="w-full rounded-lg object-cover max-h-[300px]"
                        >
                        <form 
                            method="POST" 
                            action="{{ route('posts.image.remove', ['post' => $post->id]) }}" 
                            class="absolute top-2 right-2"
                        >
                            @csrf
                            @method('DELETE')
                            <button 
                                type="submit" 
                                class="p-2 bg-red-600 hover:bg-red-700 text-white rounded-full"
                                aria-label="Remove image"
                                onclick="return confirm('Are you sure you want to remove this image?')"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
            @endif

            <!-- Image Upload -->
            <div class="mb-6">
                <x-forms.file 
                    name="image" 
                    label="Replace Image (optional)" 
                    accept="image/jpeg,image/png,image/gif,image/webp"
                    :error="$errors->first('image')"
                    hint="JPG, PNG, GIF, or WEBP. Max 2MB. Uploading a new image will replace the current one."
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
                    selected="{{ old('visibility', $post->visibility) }}"
                    :error="$errors->first('visibility')"
                />
            </div>

            <!-- Submit Buttons -->
            <div class="flex justify-end space-x-3">
                <a 
                    href="{{ route('posts.show', ['post' => $post->id]) }}" 
                    class="inline-flex items-center px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 text-sm font-medium transition-colors duration-200"
                >
                    Cancel
                </a>
                
                <x-buttons.primary type="submit" size="lg">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Update Post
                </x-buttons.primary>
            </div>
        </form>
    </div>
@endsection
