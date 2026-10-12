@extends('layouts.app')

@section('title', $user->displayName() . ' (@' . $user->username . ') - ' . config('app.name'))

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Cover Photo -->
        <div class="relative mb-8">
            @if($user->cover_photo)
                <img 
                    src="{{ $user->cover_photo }}" 
                    alt="{{ $user->displayName() }}'s cover photo" 
                    class="w-full h-64 object-cover rounded-xl"
                >
            @else
                <div class="w-full h-64 bg-gradient-to-r from-blue-500 to-purple-600 rounded-xl flex items-center justify-center">
                    <svg class="w-16 h-16 text-white opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            @endif
            
            <!-- Profile Photo -->
            <div class="absolute -bottom-16 left-4 sm:left-8">
                <div class="relative">
                    <img 
                        src="{{ $user->profile_photo }}" 
                        alt="{{ $user->displayName() }}" 
                        class="w-28 h-28 sm:w-32 sm:h-32 rounded-full border-4 border-white dark:border-gray-800 object-cover shadow-lg"
                    >
                    
                    @auth
                        @if(Auth::id() === $user->id)
                            <div class="absolute bottom-0 right-0 flex space-x-1">
                                <a 
                                    href="{{ route('profile.edit') }}" 
                                    class="p-2 bg-blue-600 hover:bg-blue-700 text-white rounded-full cursor-pointer transition-colors duration-200"
                                    aria-label="Edit profile"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                            </div>
                        @endif
                    @endauth
                </div>
            </div>
        </div>
        
        <!-- Profile Header -->
        <div class="px-4 sm:px-8 pt-16 pb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div class="mb-4 sm:mb-0">
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white">
                        {{ $user->displayName() }}
                    </h1>
                    <p class="text-gray-600 dark:text-gray-400">
                        @{{ $user->username }}
                    </p>
                </div>
                
                <div class="flex items-center space-x-4">
                    @auth
                        @if(Auth::id() === $user->id)
                            <a 
                                href="{{ route('profile.edit') }}" 
                                class="inline-flex items-center px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium transition-colors duration-200"
                            >
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                Edit Profile
                            </a>
                            
                            <a 
                                href="{{ route('settings') }}" 
                                class="inline-flex items-center px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 text-sm font-medium transition-colors duration-200"
                            >
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-.94-1.543-.826-3.31.237-2.37a1.724 1.724 0 001.065-2.572c1.756-.426 1.756-2.924 0-3.35a1.724 1.724 0 00-1.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                Settings
                            </a>
                        @endif
                    @endauth
                </div>
            </div>
            
            <!-- Bio -->
            @if($user->bio)
                <p class="mt-4 text-gray-600 dark:text-gray-400 max-w-2xl">
                    {{ $user->bio }}
                </p>
            @endif
            
            <!-- User Details -->
            <div class="mt-6 flex flex-wrap gap-6 text-sm text-gray-600 dark:text-gray-400">
                @if($user->location)
                    <div class="flex items-center space-x-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span>{{ $user->location }}</span>
                    </div>
                @endif
                
                @if($user->website)
                    <div class="flex items-center space-x-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                        <a href="{{ $user->website }}" target="_blank" class="hover:text-blue-600 dark:hover:text-blue-400">
                            {{ Str::limit($user->website, 30) }}
                        </a>
                    </div>
                @endif
                
                @if($user->birthdate)
                    <div class="flex items-center space-x-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span>Joined {{ $user->created_at->format('M Y') }}</span>
                    </div>
                @endif
                
                @if($user->age())
                    <div class="flex items-center space-x-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>{{ $user->age() }} years old</span>
                    </div>
                @endif
            </div>
        </div>
        
        <!-- Stats -->
        <div class="border-t border-gray-200 dark:border-gray-700 mt-8 pt-8">
            <div class="flex justify-center sm:justify-start space-x-8">
                <div class="text-center">
                    <p class="text-xl font-bold text-gray-900 dark:text-white">{{ $user->postCount() }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Posts</p>
                </div>
                <a href="{{ route('users.followers', ['user' => $user->username]) }}" class="text-center hover:opacity-75 transition-opacity">
                    <p class="text-xl font-bold text-gray-900 dark:text-white">{{ $user->followerCount() }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Followers</p>
                </a>
                <a href="{{ route('users.following', ['user' => $user->username]) }}" class="text-center hover:opacity-75 transition-opacity">
                    <p class="text-xl font-bold text-gray-900 dark:text-white">{{ $user->followingCount() }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Following</p>
                </a>
            </div>
        </div>
        
        <!-- Follow Button -->
        @auth
            @if(Auth::id() !== $user->id)
                <div class="flex justify-center sm:justify-start mt-4">
                    @if(Auth::user()->isFollowing($user))
                        <form method="POST" action="{{ route('unfollow', ['user' => $user->username]) }}" class="inline">
                            @csrf
                            @method('DELETE')
                            <button 
                                type="submit" 
                                class="inline-flex items-center px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 text-sm font-medium transition-colors duration-200"
                            >
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                                </svg>
                                Unfollow
                            </button>
                        </form>
                    @elseif(Auth::user()->hasPendingFollowRequestFor($user))
                        <button 
                            class="inline-flex items-center px-4 py-2 rounded-lg bg-yellow-100 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 text-yellow-700 dark:text-yellow-300 text-sm font-medium cursor-not-allowed"
                            disabled
                        >
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Requested
                        </button>
                    @else
                        <form method="POST" action="{{ route('follow', ['user' => $user->username]) }}" class="inline">
                            @csrf
                            <button 
                                type="submit" 
                                class="inline-flex items-center px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium transition-colors duration-200"
                            >
                                @if($user->is_private)
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Request to Follow
                                @else
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    Follow
                                @endif
                            </button>
                        </form>
                    @endif
                </div>
            @endif
        @endauth

        <!-- Privacy Notice -->
        @if($user->is_private)
            <div class="mt-8 p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg border border-yellow-200 dark:border-yellow-800">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <p class="text-yellow-800 dark:text-yellow-200 text-sm">
                        This is a private account. Only approved followers can see posts.
                    </p>
                </div>
            </div>
        @endif
        
        <!-- User's Posts -->
        @isset($posts)
            @if($posts->count() > 0)
                <div class="mt-8">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-6">
                        Posts
                    </h2>
                    
                    <div class="space-y-6">
                        @foreach($posts as $post)
                            <x-posts.card :post="$post" :showActions="Auth::check() && Auth::id() === $user->id" />
                        @endforeach
                    </div>
                    
                    <div class="mt-8">
                        {{ $posts->links() }}
                    </div>
                </div>
            @endif
        @endisset
    </div>
@endsection
