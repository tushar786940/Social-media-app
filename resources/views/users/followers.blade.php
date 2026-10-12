@extends('layouts.app')

@section('title', $user->displayName() . '\'s Followers - ' . config('app.name'))

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
                {{ $user->displayName() }}'s Followers
            </h1>
            
            <p class="text-gray-600 dark:text-gray-400 mt-1">
                {{ $user->followerCount() }} followers
            </p>
        </div>

        <!-- Followers List -->
        @if($followers->count() > 0)
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                @foreach($followers as $follower)
                    <div class="p-4 border-b border-gray-200 dark:border-gray-700 last:border-0">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <a href="{{ route('users.profile', ['user' => $follower->username]) }}" class="flex-shrink-0 mr-3">
                                    <img 
                                        src="{{ $follower->profile_photo }}" 
                                        alt="{{ $follower->displayName() }}" 
                                        class="w-12 h-12 rounded-full object-cover"
                                    >
                                </a>
                                
                                <div>
                                    <a 
                                        href="{{ route('users.profile', ['user' => $follower->username]) }}" 
                                        class="font-semibold text-gray-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400"
                                    >
                                        {{ $follower->displayName() }}
                                    </a>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        @{{ $follower->username }}
                                    </p>
                                </div>
                            </div>
                            
                            @auth
                                @if(Auth::id() === $user->id && $user->is_private)
                                    <!-- Profile owner can see follow status for private accounts -->
                                    <div class="flex items-center space-x-2">
                                        @if($user->isFollowedBy($follower))
                                            <span class="text-sm text-green-600 dark:text-green-400 bg-green-100 dark:bg-green-900/20 px-2 py-1 rounded-full">
                                                Following you
                                            </span>
                                        @else
                                            <span class="text-sm text-gray-500 dark:text-gray-400">
                                                Not following
                                            </span>
                                        @endif
                                    </div>
                                @endif
                            @endauth
                        </div>
                    </div>
                @endforeach
            </div>
            
            <!-- Pagination -->
            <div class="mt-8">
                {{ $followers->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <svg class="w-16 h-16 text-gray-300 dark:text-gray-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20h10M17 4H7m10 0v2c0 .656.126 1.283.356 1.857M7 4H2v2a3 3 0 005.356 1.857M7 4h10"/>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                    No followers yet
                </h3>
                <p class="text-gray-500 dark:text-gray-400">
                    @if(Auth::check() && Auth::id() === $user->id)
                        Share your profile to get followers!
                    @else
                        {{ $user->displayName() }} doesn't have any followers yet.
                    @endif
                </p>
            </div>
        @endif
    </div>
@endsection
