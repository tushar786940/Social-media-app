@props(['post', 'showCount' => true, 'showText' => false])

@php
    $user = Auth::user();
    $isLiked = $user ? $post->isLikedBy($user) : false;
    $likeCount = $post->likeCount();
@endphp

<div 
    class="flex items-center space-x-2"
    data-post-id="{{ $post->id }}"
    data-is-liked="{{ $isLiked ? 'true' : 'false' }}"
    data-like-count="{{ $likeCount }}"
>
    <!-- Like Button (Form) -->
    @if($user)
        <form 
            method="POST" 
            action="{{ $isLiked ? route('posts.unlike', ['post' => $post->id]) : route('posts.like', ['post' => $post->id]) }}"
            class="like-form inline"
            data-post-id="{{ $post->id }}"
        >
            @csrf
            @if($isLiked)
                @method('DELETE')
            @endif
            
            <button 
                type="submit"
                class="flex items-center space-x-1 p-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200 like-button"
                aria-label="{{ $isLiked ? 'Unlike' : 'Like' }} post"
                data-action="{{ $isLiked ? 'unlike' : 'like' }}"
            >
                <svg 
                    class="w-5 h-5 {{ $isLiked ? 'text-red-500 dark:text-red-400' : 'text-gray-400 dark:text-gray-500' }} like-icon"
                    fill="{{ $isLiked ? 'currentColor' : 'none' }}" 
                    stroke="currentColor" 
                    viewBox="0 0 24 24"
                >
                    <path 
                        stroke-linecap="round" 
                        stroke-linejoin="round" 
                        stroke-width="2" 
                        d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"
                    />
                </svg>
                
                @if($showText)
                    <span class="text-sm font-medium {{ $isLiked ? 'text-red-500 dark:text-red-400' : 'text-gray-600 dark:text-gray-400' }}">
                        {{ $isLiked ? 'Liked' : 'Like' }}
                    </span>
                @endif
            </button>
        </form>
    @else
        <a 
            href="{{ route('login') }}" 
            class="flex items-center space-x-1 p-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200"
            aria-label="Like post"
        >
            <svg 
                class="w-5 h-5 text-gray-400 dark:text-gray-500"
                fill="none" 
                stroke="currentColor" 
                viewBox="0 0 24 24"
            >
                <path 
                    stroke-linecap="round" 
                    stroke-linejoin="round" 
                    stroke-width="2" 
                    d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"
                />
            </svg>
        </a>
    @endif
    
    <!-- Like Count -->
    @if($showCount && $likeCount > 0)
        <span 
            class="text-sm {{ $isLiked ? 'text-red-500 dark:text-red-400' : 'text-gray-600 dark:text-gray-400' }} like-count"
            data-like-count="{{ $likeCount }}"
        >
            {{ $likeCount }}
        </span>
    @endif
</div>

@push('scripts')
<script>
// Progressive enhancement for like button (toggle without page reload)
document.addEventListener('DOMContentLoaded', function() {
    const likeForms = document.querySelectorAll('.like-form');
    
    likeForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const postId = form.dataset.postId;
            const button = form.querySelector('button');
            const icon = form.querySelector('.like-icon');
            const countEl = document.querySelector(`[data-post-id="${postId}"] .like-count`) || 
                          form.closest('[data-post-id]').querySelector('.like-count');
            
            // Get current state
            const isLiked = form.dataset.isLiked === 'true';
            const currentCount = parseInt(form.dataset.likeCount) || 0;
            
            // Optimistic UI update
            const newCount = isLiked ? currentCount - 1 : currentCount + 1;
            const newLiked = !isLiked;
            
            // Update UI immediately
            if (icon) {
                if (newLiked) {
                    icon.classList.remove('text-gray-400', 'dark:text-gray-500');
                    icon.classList.add('text-red-500', 'dark:text-red-400');
                    icon.setAttribute('fill', 'currentColor');
                } else {
                    icon.classList.remove('text-red-500', 'dark:text-red-400');
                    icon.classList.add('text-gray-400', 'dark:text-gray-500');
                    icon.setAttribute('fill', 'none');
                }
            }
            
            if (countEl) {
                countEl.textContent = newCount;
                countEl.classList.toggle('text-red-500', newLiked);
                countEl.classList.toggle('text-red-400', newLiked);
                countEl.classList.toggle('dark:text-red-400', newLiked);
                countEl.classList.toggle('text-gray-600', !newLiked);
                countEl.classList.toggle('dark:text-gray-400', !newLiked);
            }
            
            // Update form action and data attributes
            form.action = newLiked 
                ? `/posts/${postId}/like` 
                : `/posts/${postId}/like`;
            form.dataset.isLiked = newLiked;
            form.dataset.likeCount = newCount;
            
            // If method is DELETE, change to POST or vice versa
            const methodInput = form.querySelector('input[name="_method"]');
            if (methodInput) {
                form.removeChild(methodInput);
            }
            if (!newLiked) {
                const methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.value = 'DELETE';
                form.appendChild(methodInput);
            }
            
            // Submit the form via fetch for the actual request
            fetch(form.action, {
                method: newLiked ? 'POST' : 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({}),
            })
            .then(response => response.json())
            .then(data => {
                // Update with actual server response if needed
                if (data.likeCount !== undefined) {
                    form.dataset.likeCount = data.likeCount;
                    if (countEl) {
                        countEl.textContent = data.likeCount;
                    }
                }
            })
            .catch(error => {
                // Revert UI on error
                console.error('Error:', error);
                // In a real app, you might want to revert the UI here
            });
        });
    });
});
</script>
@endpush
