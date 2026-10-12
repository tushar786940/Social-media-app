@props(['post', 'parent' => null])

@php
    $user = Auth::user();
    $canComment = $user && $post->canEngage($user);
@endphp

@if($canComment)
    <form 
        method="POST" 
        action="{{ route('comments.store', ['post' => $post->id]) }}"
        class="mt-6 comment-form"
        data-post-id="{{ $post->id }}"
        {{ $parent ? 'data-parent-id=' . $parent->id : '' }}
    >
        @csrf
        
        @if($parent)
            <input type="hidden" name="parent_id" value="{{ $parent->id }}">
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                Replying to <a href="{{ $parent->authorUrl() }}" class="text-blue-600 dark:text-blue-400 hover:underline">
                    {{ $parent->authorName() }}
                </a>
            </p>
        @endif
        
        <div class="flex items-start space-x-3">
            <img 
                src="{{ $user->profile_photo }}" 
                alt="{{ $user->displayName() }}" 
                class="w-10 h-10 rounded-full object-cover flex-shrink-0"
            >
            
            <div class="flex-1">
                <textarea 
                    name="content" 
                    rows="2"
                    placeholder="{{ $parent ? 'Write a reply...' : 'Write a comment...' }}"
                    class="w-full p-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:outline-none resize-none"
                    required
                    minlength="1"
                    maxlength="2000"
                >{{ old('content') }}</textarea>
                
                <div class="flex justify-end items-center mt-2">
                    <x-buttons.primary 
                        type="submit" 
                        size="sm"
                        class="text-xs"
                    >
                        {{ $parent ? 'Reply' : 'Comment' }}
                    </x-buttons.primary>
                </div>
                
                @error('content')
                    <p class="mt-2 text-sm text-red-600 dark:text-red-400" role="alert">
                        {{ $message }}
                    </p>
                @enderror
            </div>
        </div>
    </form>
@else
    <div class="mt-6 p-4 bg-gray-100 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
        <p class="text-sm text-gray-600 dark:text-gray-400 text-center">
            <a href="{{ route('login') }}" class="text-blue-600 dark:text-blue-400 hover:underline">
                Sign in
            </a> 
            to comment on this post.
        </p>
    </div>
@endif
