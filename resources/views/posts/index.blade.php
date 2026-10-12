@extends('layouts.app')

@section('title', 'Posts Feed - ' . config('app.name'))

@section('content')
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    Posts Feed
                </h1>
                <p class="text-gray-600 dark:text-gray-400 mt-1">
                    Recent posts from users you follow
                </p>
            </div>
            
            @auth
                <a 
                    href="{{ route('posts.create') }}" 
                    class="inline-flex items-center px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium transition-colors duration-200"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Create Post
                </a>
            @endauth
        </div>

        <!-- Posts -->
        @if($posts->count() > 0)
            <div class="space-y-6">
                @foreach($posts as $post)
                    <x-posts.card :post="$post" :showActions="true" />
                @endforeach
            </div>
            
            <!-- Pagination -->
            <div class="mt-8">
                {{ $posts->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <svg class="w-16 h-16 text-gray-300 dark:text-gray-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                    No posts yet
                </h3>
                <p class="text-gray-500 dark:text-gray-400">
                    @auth
                        View posts from users you follow on your <a href="{{ route('feed') }}" class="text-blue-600 dark:text-blue-400 hover:underline">Feed</a>.
                    @else
                        Sign in to see posts from your network.
                    @endauth
                </p>
                
                @auth
                    <div class="mt-6">
                        <a 
                            href="{{ route('posts.create') }}" 
                            class="inline-flex items-center px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium transition-colors duration-200"
                        >
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                            Create Your First Post
                        </a>
                    </div>
                @endauth
            </div>
        @else
            <div class="text-center py-12">
                <svg class="w-16 h-16 text-gray-300 dark:text-gray-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                    No posts yet
                </h3>
                <p class="text-gray-500 dark:text-gray-400">
                    @auth
                        Be the first to create a post!
                    @else
                        Sign in to see posts from your network.
                    @endauth
                </p>
                
                @auth
                    <div class="mt-6">
                        <a 
                            href="{{ route('posts.create') }}" 
                            class="inline-flex items-center px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium transition-colors duration-200"
                        >
                            Create Your First Post
                        </a>
                    </div>
                @endauth
            </div>
        @endif
    </div>
@endsection
