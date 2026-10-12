@extends('layouts.app')

@section('title', '429 - Too Many Requests')

@section('content')
    <div class="min-h-screen flex items-center justify-center px-4 py-12 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8 text-center">
            <!-- Error Code -->
            <div class="text-8xl font-extrabold text-orange-600 dark:text-orange-400">
                429
            </div>
            
            <!-- Error Message -->
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                Too Many Requests
            </h1>
            
            <p class="text-gray-600 dark:text-gray-400">
                You've made too many requests. Please wait a moment and try again.
            </p>
            
            <!-- Illustration -->
            <div class="my-8">
                <svg class="w-48 h-48 mx-auto text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 18h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
            </div>
            
            <!-- Actions -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <button 
                    onclick="setTimeout(() => location.reload(), 5000)"
                    class="inline-flex items-center justify-center px-6 py-3 rounded-lg bg-orange-600 text-white font-medium hover:bg-orange-700 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900"
                >
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Try Again (5s)
                </button>
                
                <a 
                    href="{{ route('home') }}" 
                    class="inline-flex items-center justify-center px-6 py-3 rounded-lg bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 font-medium hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2"
                >
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    Go Home
                </a>
            </div>
        </div>
    </div>
    
    <script>
        // Auto-redirect after 5 seconds
        setTimeout(() => {
            window.location.reload();
        }, 5000);
    </script>
@endsection
