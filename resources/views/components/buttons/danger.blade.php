@props(['type' => 'button', 'size' => 'md', 'disabled' => false, 'loading' => false, 'full' => false])

@php
    $sizes = [
        'sm' => 'px-3 py-1.5 text-sm',
        'md' => 'px-4 py-2 text-sm',
        'lg' => 'px-6 py-3 text-base',
    ];
    
    $width = $full ? 'w-full' : '';
    $disabledClasses = $disabled || $loading ? 'opacity-50 cursor-not-allowed' : 'hover:bg-red-700 active:bg-red-800';
@endphp

<button 
    {{ $disabled ? 'disabled' : '' }}
    type="{{ $type }}"
    class="inline-flex items-center justify-center {{ $width }} {{ $sizes[$size] }} rounded-lg bg-red-600 text-white font-medium transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 {{ $disabledClasses }}"
    {{ $attributes }}
>
    @if($loading)
        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
    @endif
    
    {{ $slot }}
</button>
