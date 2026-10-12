@extends('layouts.app')

@section('title', 'Post by ' . $post->authorName() . ' - ' . config('app.name'))

@section('content')
    @php
        $comments = $post->topLevelComments()->with(['user', 'replies' => function($query) {
            $query->with('user')->newest();
        }])->newest()->paginate(10);
    @endphp
    
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <a 
                href="{{ route('posts.index') }}" 
                class="inline-flex items-center text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 mb-4"
            >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Back to Feed
            </a>
            
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                Post
            </h1>
        </div>

        <!-- Post Card -->
        <x-posts.card :post="$post" :showActions="true" />

        <!-- Comments Section -->
        <div class="mt-8">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                Comments ({{ $post->commentCount() }})
            </h2>
            
            <!-- Comment Form -->
            @auth
                @if($post->canEngage(Auth::user()))
                    <x-posts.comment-form :post="$post" />
                @else
                    <div class="mb-6 p-4 bg-gray-100 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                        <p class="text-sm text-gray-600 dark:text-gray-400 text-center">
                            You cannot comment on this post.
                        </p>
                    </div>
                @endif
            @else
                <div class="mb-6 p-4 bg-gray-100 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                    <p class="text-sm text-gray-600 dark:text-gray-400 text-center">
                        <a href="{{ route('login') }}" class="text-blue-600 dark:text-blue-400 hover:underline">
                            Sign in
                        </a> 
                        to comment on this post.
                    </p>
                </div>
            @endauth
            
            <!-- Comments List -->
            @if($comments->count() > 0)
                <div class="mt-6 space-y-4" id="comments-list">
                    @foreach($comments as $comment)
                        <x-posts.comment :comment="$comment" :post="$post" />
                    @endforeach
                </div>
                
                <!-- Pagination -->
                <div class="mt-8">
                    {{ $comments->links() }}
                </div>
            @endif
        </div>

        <!-- Post Actions (for owner) -->
        @auth
            @if(Auth::id() === $post->user_id)
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 mt-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        Post Actions
                    </h3>
                    
                    <div class="flex space-x-4">
                        <a 
                            href="{{ $post->editUrl() }}" 
                            class="inline-flex items-center px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 text-sm font-medium transition-colors duration-200"
                        >
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            Edit Post
                        </a>
                        
                        <form method="POST" action="{{ route('posts.destroy', ['post' => $post->id]) }}" class="inline">
                            @csrf
                            @method('DELETE')
                            <button 
                                type="submit" 
                                class="inline-flex items-center px-4 py-2 rounded-lg bg-red-600 hover:bg-red-700 text-white text-sm font-medium transition-colors duration-200"
                                onclick="return confirm('Are you sure you want to delete this post?')"
                            >
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                Delete Post
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        @endauth
    </div>
@endsection
