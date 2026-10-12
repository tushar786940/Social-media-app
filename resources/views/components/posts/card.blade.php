@props(['post', 'showActions' => true])

<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden mb-6">
    <!-- Post Header -->
    <div class="flex items-center p-4 border-b border-gray-200 dark:border-gray-700">
        <a href="{{ $post->authorUrl() }}" class="flex-shrink-0 mr-3">
            <img 
                src="{{ $post->authorPhoto() }}" 
                alt="{{ $post->authorName() }}" 
                class="w-10 h-10 rounded-full object-cover"
            >
        </a>
        
        <div>
            <a 
                href="{{ $post->authorUrl() }}" 
                class="font-semibold text-gray-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400"
            >
                {{ $post->authorName() }}
            </a>
            <div class="flex items-center text-sm text-gray-500 dark:text-gray-400">
                <span>{{ $post->authorUsername() }}</span>
                <span class="mx-1">•</span>
                <span>{{ $post->relativeDate() }}</span>
                @if($post->visibility === 'private')
                    <span class="ml-2 text-xs bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded">
                        Private
                    </span>
                @endif
            </div>
        </div>
        
        @if($showActions && Auth::check() && Auth::id() === $post->user_id)
            <div class="ml-auto flex space-x-2">
                <a 
                    href="{{ $post->editUrl() }}" 
                    class="p-2 text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700"
                    aria-label="Edit post"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                </a>
                
                <form method="POST" action="{{ route('posts.destroy', ['post' => $post->id]) }}" class="inline">
                    @csrf
                    @method('DELETE')
                    <button 
                        type="submit" 
                        class="p-2 text-gray-400 hover:text-red-600 dark:hover:text-red-400 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700"
                        aria-label="Delete post"
                        onclick="return confirm('Are you sure you want to delete this post?')"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </form>
            </div>
        @endif
    </div>
    
    <!-- Post Content -->
    <div class="p-4">
        <p class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap">
            {{ $post->content }}
        </p>
    </div>
    
    <!-- Post Image -->
    @if($post->hasImage())
        <div class="px-4 pb-4">
            <img 
                src="{{ $post->image_url }}" 
                alt="Post image" 
                class="w-full rounded-lg object-cover max-h-[500px]"
            >
        </div>
    @endif
    
    <!-- Engagement Section -->
    <div class="p-4 border-t border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
            <!-- Left side: Like and Comment actions -->
            <div class="flex items-center space-x-6">
                <!-- Like Button -->
                <x-posts.like-button :post="$post" :showCount="true" />
                
                <!-- Comment Button -->
                <a 
                    href="{{ route('posts.comments.index', ['post' => $post->id]) }}" 
                    class="flex items-center space-x-2 text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors duration-200"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                    <span class="text-sm">{{ $post->commentCount() }}</span>
                </a>
            </div>
            
            <!-- Right side: Timestamp and visibility -->
            <div class="flex items-center space-x-2 text-sm text-gray-500 dark:text-gray-400">
                <span>{{ $post->relativeDate() }}</span>
                <span class="capitalize">{{ $post->visibilityLabel() }}</span>
            </div>
        </div>
    </div>
</div>
