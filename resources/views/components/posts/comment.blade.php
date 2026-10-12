@props(['comment', 'post', 'level' => 0])

@php
    $user = Auth::user();
    $canEdit = $user && $user->id === $comment->user_id;
    $maxLevel = 3; // Maximum nesting level for replies
    $showReplyForm = false;
@endphp

<div 
    class="flex items-start space-x-3 mt-4 comment-item"
    data-comment-id="{{ $comment->id }}"
    style="margin-left: {{ $level * 24 }}px;"
>
    <!-- Author Avatar -->
    <a href="{{ $comment->authorUrl() }}" class="flex-shrink-0">
        <img 
            src="{{ $comment->authorPhoto() }}" 
            alt="{{ $comment->authorName() }}" 
            class="w-10 h-10 rounded-full object-cover"
        >
    </a>
    
    <!-- Comment Content -->
    <div class="flex-1 bg-gray-50 dark:bg-gray-800 rounded-lg p-3">
        <!-- Comment Header -->
        <div class="flex items-center justify-between mb-2">
            <div>
                <a 
                    href="{{ $comment->authorUrl() }}" 
                    class="font-semibold text-gray-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400"
                >
                    {{ $comment->authorName() }}
                </a>
                <span class="text-sm text-gray-500 dark:text-gray-400 ml-1">
                    {{ $comment->relativeDate() }}
                </span>
            </div>
            
            <!-- Comment Actions -->
            @if($canEdit)
                <div class="flex items-center space-x-2" x-data="{ open: false }">
                    <button 
                        @click="open = !open"
                        class="p-1 rounded-full hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                        aria-label="Comment actions"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/>
                        </svg>
                    </button>
                    
                    <div 
                        x-show="open" 
                        @click.away="open = false"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="transform opacity-0 scale-95"
                        x-transition:enter-end="transform opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="transform opacity-100 scale-100"
                        x-transition:leave-end="transform opacity-0 scale-95"
                        class="absolute z-10 mt-2 w-40 rounded-md bg-white dark:bg-gray-800 shadow-lg ring-1 ring-black ring-opacity-5"
                        role="menu"
                    >
                        <div class="py-1" role="none">
                            <!-- Edit Comment -->
                            <a 
                                href="#"
                                @click.prevent="
                                    open = false;
                                    const form = document.querySelector('.comment-form[data-post-id=\"{{ $post->id }}\"]');
                                    if (form) {
                                        form.dataset.editingCommentId = '{{ $comment->id }}';
                                        form.dataset.originalContent = @js(addslashes($comment->content));
                                        form.querySelector('textarea').value = @js(addslashes($comment->content));
                                        form.querySelector('button[type=\"submit\"]').textContent = 'Update';
                                    }
                                " 
                                class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700"
                                role="menuitem"
                            >
                                Edit
                            </a>
                            
                            <div class="border-t border-gray-200 dark:border-gray-600 my-1"></div>
                            
                            <!-- Delete Comment -->
                            <form method="POST" action="{{ route('comments.destroy', ['post' => $post->id, 'comment' => $comment->id]) }}" class="block">
                                @csrf
                                @method('DELETE')
                                <button 
                                    type="submit" 
                                    class="w-full text-left block px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20"
                                    role="menuitem"
                                    onclick="return confirm('Are you sure you want to delete this comment?')"
                                >
                                    Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endif
        </div>
        
        <!-- Comment Text (Escaped for security) -->
        <p class="text-gray-700 dark:text-gray-300 text-sm whitespace-pre-wrap">
            {{ $comment->escapedContent }}
        </p>
        
        <!-- Reply Button -->
        @if($level < $maxLevel)
            <button 
                class="mt-2 text-xs text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 flex items-center space-x-1 reply-button"
                data-comment-id="{{ $comment->id }}"
                onclick="
                    const form = this.closest('.comment-item').querySelector('.reply-form');
                    if (form) {
                        form.classList.toggle('hidden');
                    } else {
                        // Create or show reply form
                        const container = this.closest('.comment-item');
                        const replyFormHtml = `
                            <div class=\"mt-3 reply-form hidden\">
                                <x-posts.comment-form :post=\"$post\" :parent=\"{{ $comment->id }}\" />
                            </div>
                        `;
                        container.insertAdjacentHTML('beforeend', replyFormHtml);
                        container.querySelector('.reply-form').classList.remove('hidden');
                    }
                    this.classList.add('hidden');
                "
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10M7 15h1a2 2 0 002-2V7a2 2 0 012-2h2a2 2 0 012 2v6a2 2 0 002 2h1M12 20h8"/>
                </svg>
                <span>Reply</span>
            </button>
        @endif
    </div>
</div>

<!-- Reply Form (Hidden by default) -->
<div class="reply-form hidden mt-3" data-comment-id="{{ $comment->id }}">
    <x-posts.comment-form :post="$post" :parent="$comment" />
</div>

<!-- Replies (Threaded) -->
@foreach($comment->replies->sortByDesc('created_at') as $reply)
    <x-posts.comment :comment="$reply" :post="$post" :level="$level + 1" />
@endforeach
