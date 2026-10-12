@extends('layouts.app')

@section('title', 'Following - ' . $user->displayName() . ' - ' . config('app.name'))

@section('content')
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <a 
                href="{{ route('users.profile', ['user' => $user->username]) }}" 
                class="inline-flex items-center text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 mb-4"
            >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Back to Profile
            </a>
            
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                Following
            </h1>
            
            <p class="text-gray-600 dark:text-gray-400 mt-1">
                {{ $user->followingCount() }} following
            </p>
        </div>

        <!-- Following List -->
        @if($following->count() > 0)
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                @foreach($following as $followingUser)
                    <div class="p-4 border-b border-gray-200 dark:border-gray-700 last:border-0">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <a href="{{ route('users.profile', ['user' => $followingUser->username]) }}" class="flex-shrink-0 mr-3">
                                    <img 
                                        src="{{ $followingUser->profile_photo }}" 
                                        alt="{{ $followingUser->displayName() }}" 
                                        class="w-12 h-12 rounded-full object-cover"
                                    >
                                </a>
                                
                                <div>
                                    <a 
                                        href="{{ route('users.profile', ['user' => $followingUser->username]) }}" 
                                        class="font-semibold text-gray-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400"
                                    >
                                        {{ $followingUser->displayName() }}
                                    </a>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        @{{ $followingUser->username }}
                                    </p>
                                </div>
                            </div>
                            
                            @auth
                                @if(Auth::id() === $user->id)
                                    <!-- Profile owner can unfollow -->
                                    <form method="POST" action="{{ route('unfollow', ['user' => $followingUser->username]) }}" class="flex">
                                        @csrf
                                        @method('DELETE')
                                        <button 
                                            type="submit" 
                                            class="px-3 py-1 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm rounded-full transition-colors duration-200"
                                        >
                                            Unfollow
                                        </button>
                                    </form>
                                @endif
                            @endauth
                        </div>
                    </div>
                @endforeach
            </div>
            
            <!-- Pagination -->
            <div class="mt-8">
                {{ $following->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <svg class="w-16 h-16 text-gray-300 dark:text-gray-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                    Not following anyone
                </h3>
                <p class="text-gray-500 dark:text-gray-400">
                    @if(Auth::check() && Auth::id() === $user->id)
                        Start following users to see their posts in your feed!
                    @else
                        {{ $user->displayName() }} isn't following anyone yet.
                    @endif
                </p>
                
                @auth
                    @if(Auth::id() === $user->id)
                        <div class="mt-6">
                            <a 
                                href="{{ route('posts.index') }}" 
                                class="inline-flex items-center px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium transition-colors duration-200"
                            >
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                                Discover Users
                            </a>
                        </div>
                    @endif
                @endauth
            </div>
        @endif
    </div>
@endsection
