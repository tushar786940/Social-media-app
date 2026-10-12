@extends('layouts.app')

@section('title', 'Verify Email - ' . config('app.name'))

@section('content')
    <div class="min-h-screen flex items-center justify-center px-4 py-12 sm:px-6 lg:px-8">
        <div class="w-full max-w-md space-y-8 text-center">
            <!-- Logo -->
            <div class="text-center">
                <a href="{{ route('home') }}" class="flex items-center justify-center space-x-2 mb-6 mx-auto">
                    <svg class="h-12 w-12 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/>
                    </svg>
                    <span class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ config('app.name', 'Social') }}
                    </span>
                </a>
                <h2 class="text-3xl font-bold text-gray-900 dark:text-white">
                    Verify Your Email
                </h2>
            </div>

            <!-- Status Message -->
            @if (session('status') == 'verification-link-sent')
                <x-alerts.flash type="success" message="A new verification link has been sent to your email address." />
            @endif

            <!-- Verification Notice -->
            <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-6 border border-gray-200 dark:border-gray-700">
                <p class="text-gray-600 dark:text-gray-400 mb-4">
                    Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you?
                    If you didn't receive the email, we will gladly send you another.
                </p>

                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <form method="POST" action="{{ route('verification.send') }}" class="inline-block">
                        @csrf
                        <x-buttons.secondary type="submit">
                            Resend Verification Email
                        </x-buttons.secondary>
                    </form>

                    <form method="POST" action="{{ route('logout') }}" class="inline-block">
                        @csrf
                        <x-buttons.danger type="submit">
                            Logout
                        </x-buttons.danger>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
