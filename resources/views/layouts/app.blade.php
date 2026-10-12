<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="@yield('meta_description', config('app.name', 'Social Media App'))">
    
    <title>@yield('title', config('app.name', 'Social Media App'))</title>
    
    <!-- Fonts -->
    @fonts
    
    <!-- Favicon -->
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    
    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Preload critical assets -->
    <link rel="preload" href="{{ asset('fonts/Inter.woff2') }}" as="font" type="font/woff2" crossorigin>
    
    @stack('styles')
</head>
<body class="antialiased bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100">
    <!-- Skip to main content -->
    <a href="#main-content" class="sr-only focus:not-sr-only focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
        Skip to main content
    </a>
    
    <!-- Loading indicator -->
    <div id="loading-indicator" class="fixed inset-0 z-50 flex items-center justify-center bg-white/80 dark:bg-gray-900/80 backdrop-blur-sm hidden">
        <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-blue-500 text-white animate-spin">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>
    </div>
    
    <!-- Navigation -->
    @include('layouts.partials.header')
    
    <!-- Flash Messages -->
    @include('components.alerts.flash')
    
    <!-- Main Content -->
    <main id="main-content" class="min-h-screen">
        @yield('content')
    </main>
    
    <!-- Footer -->
    @include('layouts.partials.footer')
    
    <!-- Back to top button -->
    <button 
        id="back-to-top" 
        class="fixed bottom-6 right-6 z-40 p-3 rounded-full bg-blue-600 text-white shadow-lg hover:bg-blue-700 transition-colors duration-200 hidden focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
        aria-label="Back to top"
    >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
        </svg>
    </button>
    
    <!-- Scripts -->
    @stack('scripts')
    
    <!-- Initialize application -->
    <script>
        // Back to top functionality
        const backToTopButton = document.getElementById('back-to-top');
        
        window.addEventListener('scroll', () => {
            if (window.pageYOffset > 300) {
                backToTopButton.classList.remove('hidden');
            } else {
                backToTopButton.classList.add('hidden');
            }
        });
        
        backToTopButton.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
        
        // Loading indicator for form submissions
        document.addEventListener('DOMContentLoaded', () => {
            const forms = document.querySelectorAll('form[data-loading]');
            forms.forEach(form => {
                form.addEventListener('submit', () => {
                    const loadingIndicator = document.getElementById('loading-indicator');
                    if (loadingIndicator) {
                        loadingIndicator.classList.remove('hidden');
                    }
                });
            });
        });
    </script>
</body>
</html>
