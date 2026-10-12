@props(['label', 'value', 'icon' => null, 'color' => 'blue'])

@php
    $colors = [
        'blue' => 'text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20',
        'green' => 'text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-900/20',
        'red' => 'text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/20',
        'yellow' => 'text-yellow-600 dark:text-yellow-400 bg-yellow-50 dark:bg-yellow-900/20',
        'purple' => 'text-purple-600 dark:text-purple-400 bg-purple-50 dark:bg-purple-900/20',
        'gray' => 'text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/20',
    ];
    
    $colorClass = $colors[$color] ?? $colors['blue'];
@endphp

<div class="flex items-center space-x-3 p-4 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
    @if($icon)
        <div class="flex-shrink-0 p-2 rounded-lg {{ $colorClass }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                {{ $icon }}
            </svg>
        </div>
    @endif
    
    <div>
        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $value }}</p>
        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $label }}</p>
    </div>
</div>
