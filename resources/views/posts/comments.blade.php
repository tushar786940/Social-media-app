@extends('layouts.app')

@section('title', 'Comments - ' . config('app.name'))

@section('content')
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <a 
                href="{{ route('posts.show', ['post' => $post->id]) }}" 
                class="inline-flex items-center text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 mb-4"
            >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Back to Post
            </a>
            
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                Comments ({{ $post->commentCount() }})
            </h1>
        </div>

        <!-- Post Card (for context) -->
        <x-posts.card :post="$post" :showActions="false" class="mb-8" />

        <!-- Comment Form -->
        @auth
            <x-posts.comment-form :post="$post" />
        @endauth

        <!-- Comments List -->
        @if($comments->count() > 0)
            <div class="mt-8 space-y-4" id="comments-list">
                @foreach($comments as $comment)
                    <x-posts.comment :comment="$comment" :post="$post" />
                @endforeach
            </div>
            
            <!-- Pagination -->
            <div class="mt-8">
                {{ $comments->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <svg class="w-16 h-16 text-gray-300 dark:text-gray-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                    No comments yet
                </h3>
                <p class="text-gray-500 dark:text-gray-400">
                    Be the first to comment on this post.
                </p>
            </div>
        @endif
    </div>

    @push('scripts')
    <script>
        // Handle reply form toggling
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle reply forms
            const replyButtons = document.querySelectorAll('.reply-button');
            replyButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const commentId = this.dataset.commentId;
                    const replyForm = document.querySelector(`.reply-form[data-comment-id="${commentId}"]`);
                    
                    if (replyForm) {
                        replyForm.classList.toggle('hidden');
                        this.classList.toggle('hidden');
                    }
                });
            });
        });
    </script>
    @endpush
@endsection
