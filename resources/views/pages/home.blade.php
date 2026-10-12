@extends('layouts.app')

@section('title', 'Home - ' . config('app.name'))

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Welcome Section -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
                Welcome to {{ config('app.name', 'Social') }}
            </h1>
            <p class="text-gray-600 dark:text-gray-400">
                Connect with friends, share your thoughts, and discover new content.
            </p>
        </div>
        
        <!-- Feature Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            <!-- Quick Actions -->
            <x-cards.base title="Quick Actions">
                <div class="space-y-4">
                    @auth
                        <a 
                            href="{{ route('posts.create') }}" 
                            class="flex items-center space-x-3 p-3 rounded-lg bg-gray-50 dark:bg-gray-900/50 hover:bg-gray-100 dark:hover:bg-gray-900/80 transition-colors duration-200"
                        >
                            <div class="flex-shrink-0 p-2 rounded-lg bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900 dark:text-white">Create Post</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Share your thoughts</p>
                            </div>
                        </a>
                        
                        <a 
                            href="{{ route('users.profile', ['user' => auth()->user()->username]) }}" 
                            class="flex items-center space-x-3 p-3 rounded-lg bg-gray-50 dark:bg-gray-900/50 hover:bg-gray-100 dark:hover:bg-gray-900/80 transition-colors duration-200"
                        >
                            <div class="flex-shrink-0 p-2 rounded-lg bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900 dark:text-white">My Profile</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">View your profile</p>
                            </div>
                        </a>
                    @else
                        <a 
                            href="{{ route('login') }}" 
                            class="flex items-center space-x-3 p-3 rounded-lg bg-gray-50 dark:bg-gray-900/50 hover:bg-gray-100 dark:hover:bg-gray-900/80 transition-colors duration-200"
                        >
                            <div class="flex-shrink-0 p-2 rounded-lg bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900 dark:text-white">Login</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Sign in to continue</p>
                            </div>
                        </a>
                        
                        <a 
                            href="{{ route('register') }}" 
                            class="flex items-center space-x-3 p-3 rounded-lg bg-gray-50 dark:bg-gray-900/50 hover:bg-gray-100 dark:hover:bg-gray-900/80 transition-colors duration-200"
                        >
                            <div class="flex-shrink-0 p-2 rounded-lg bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900 dark:text-white">Register</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Create an account</p>
                            </div>
                        </a>
                    @endauth
                </div>
            </x-cards.base>
            
            <!-- Stats -->
            <x-cards.base title="Platform Stats">
                <div class="grid grid-cols-2 gap-4">
                    <x-cards.stat 
                        label="Users" 
                        value="{{ number_format(12500, 0) }}" 
                        icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>'
                        color="blue"
                    />
                    <x-cards.stat 
                        label="Posts" 
                        value="{{ number_format(45000, 0) }}" 
                        icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>'
                        color="green"
                    />
                    <x-cards.stat 
                        label="Comments" 
                        value="{{ number_format(120000, 0) }}" 
                        icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>'
                        color="purple"
                    />
                    <x-cards.stat 
                        label="Likes" 
                        value="{{ number_format(500000, 0) }}" 
                        icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>'
                        color="red"
                    />
                </div>
            </x-cards.base>
            
            <!-- Trending Topics -->
            <x-cards.base title="Trending Topics">
                <div class="space-y-3">
                    @for($i = 1; $i <= 5; $i++)
                        <a href="#" class="block p-3 rounded-lg bg-gray-50 dark:bg-gray-900/50 hover:bg-gray-100 dark:hover:bg-gray-900/80 transition-colors duration-200">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-white">#TrendingTopic{{ $i }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ rand(100, 5000) }} posts</p>
                                </div>
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </div>
                        </a>
                    @endfor
                </div>
            </x-cards.base>
        </div>
        
        <!-- CTA Section -->
        @guest
            <div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-2xl p-8 text-white text-center">
                <h2 class="text-2xl font-bold mb-2">
                    Join the Community
                </h2>
                <p class="mb-6 opacity-90">
                    Create your account and start connecting with others today.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a 
                        href="{{ route('register') }}" 
                        class="inline-flex items-center justify-center px-6 py-3 rounded-lg bg-white text-blue-600 font-medium hover:bg-gray-100 transition-colors duration-200"
                    >
                        Get Started
                    </a>
                    <a 
                        href="{{ route('login') }}" 
                        class="inline-flex items-center justify-center px-6 py-3 rounded-lg bg-transparent border-2 border-white text-white font-medium hover:bg-white/10 transition-colors duration-200"
                    >
                        Sign In
                    </a>
                </div>
            </div>
        @endguest
    </div>
@endsection
