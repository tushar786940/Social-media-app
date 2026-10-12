@props(['type' => 'button', 'size' => 'md', 'disabled' => false, 'loading' => false, 'variant' => 'primary'])

@php
    $sizes = [
        'sm' => 'p-1.5',
        'md' => 'p-2',
        'lg' => 'p-3',
    ];
    
    $variants = [
        'primary' => 'text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20',
        'secondary' => 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800',
        'danger' => 'text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20',
        'success' => 'text-green-600 dark:text-green-400 hover:bg-green-50 dark:hover:bg-green-900/20',
        'ghost' => 'text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800',
    ];
    
    $disabledClasses = $disabled || $loading ? 'opacity-50 cursor-not-allowed' : $variants[$variant];
@endphp

<button 
    {{ $disabled ? 'disabled' : '' }}
    type="{{ $type }}"
    class="inline-flex items-center justify-center {{ $sizes[$size] }} rounded-lg transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 {{ $disabledClasses }}"
    {{ $attributes }}
>
    @if($loading)
        <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
    @else
        {{ $slot }}
    @endif
</button>
