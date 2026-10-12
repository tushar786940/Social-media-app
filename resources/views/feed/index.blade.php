@extends('layouts.app')

@section('title')
    @if($feedType === 'home')
        Your Feed - {{ config('app.name') }}
    @elseif($feedType === 'discover')
        Discover - {{ config('app.name') }}
    @else
        Feed - {{ config('app.name') }}
    @endif
@endsection

@section('content')
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                @if($feedType === 'home')
                    Your Feed
                @elseif($feedType === 'discover')
                    Discover Posts
                @else
                    Feed
                @endif
            </h1>
            
            <p class="text-gray-600 dark:text-gray-400 mt-1">
                @if($feedType === 'home')
                    Posts from users you follow
                @elseif($feedType === 'discover')
                    Public posts from public accounts
                @endif
            </p>
        </div>

        <!-- Feed Navigation -->
        @auth
            <div class="flex items-center space-x-4 mb-8">
                <a 
                    href="{{ route('feed') }}" 
                    class="px-4 py-2 rounded-lg {{ $feedType === 'home' ? 'bg-blue-600 text-white' : 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700' }} text-sm font-medium transition-colors duration-200"
                >
                    Your Feed
                </a>
                
                <a 
                    href="{{ route('discover') }}" 
                    class="px-4 py-2 rounded-lg {{ $feedType === 'discover' ? 'bg-blue-600 text-white' : 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700' }} text-sm font-medium transition-colors duration-200"
                >
                    Discover
                </a>
            </div>
        @endauth

        <!-- Create Post Button (for authenticated users) -->
        @auth
            <div class="mb-8">
                <a 
                    href="{{ route('posts.create') }}" 
                    class="inline-flex items-center px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium transition-colors duration-200"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Create Post
                </a>
            </div>
        @endauth

        <!-- Posts Feed -->
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
            <!-- Empty Feed State -->
            <div class="text-center py-12">
                @if($feedType === 'home')
                    <svg class="w-16 h-16 text-gray-300 dark:text-gray-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                        Your feed is empty
                    </h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-6">
                        Start following users to see their posts here.
                    </p>
                    
                    <div class="space-y-4">
                        <a 
                            href="{{ route('discover') }}" 
                            class="inline-flex items-center px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium transition-colors duration-200"
                        >
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            Browse Discover
                        </a>
                        
                        <a 
                            href="{{ route('users.profile', ['user' => Auth::user()->username]) }}" 
                            class="inline-block px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 text-sm font-medium transition-colors duration-200"
                        >
                            View Your Profile
                        </a>
                    </div>
                @elseif($feedType === 'discover')
                    <svg class="w-16 h-16 text-gray-300 dark:text-gray-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                        No public posts yet
                    </h3>
                    <p class="text-gray-500 dark:text-gray-400">
                        Be the first to create a public post!
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
                                Create Post
                            </a>
                        </div>
                    @endauth
                @else
                    <svg class="w-16 h-16 text-gray-300 dark:text-gray-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                        No posts to show
                    </h3>
                    <p class="text-gray-500 dark:text-gray-400">
                        There are no posts matching your criteria.
                    </p>
                @endif
            </div>
        @endif
    </div>
@endsection
