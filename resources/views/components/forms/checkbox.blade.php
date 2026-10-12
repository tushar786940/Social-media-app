@props(['name', 'value' => null, 'label' => null, 'checked' => false, 'disabled' => false, 'error' => null, 'hint' => null, 'id' => null])

@php
    $id = $id ?? $name;
    $oldValue = old($name, $checked);
    $isChecked = $oldValue === true || $oldValue === 'on' || $oldValue === $value || $oldValue === '1';
    $hasError = $error || ($errors && $errors->has($name));
    $errorMessage = $error ?? ($errors->first($name) ?? null);
    
    $inputClasses = 'h-4 w-4 rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 dark:focus:ring-offset-gray-900 dark:bg-gray-800';
    $inputClasses .= $hasError ? ' border-red-500 dark:border-red-500' : '';
    $inputClasses .= $disabled ? ' bg-gray-100 dark:bg-gray-700 cursor-not-allowed' : '';
@endphp

<div class="mb-4">
    <div class="flex items-center">
        <input 
            type="checkbox"
            id="{{ $id }}"
            name="{{ $name }}"
            value="{{ $value ?? 'on' }}"
            {{ $isChecked ? 'checked' : '' }}
            {{ $disabled ? 'disabled' : '' }}
            class="{{ $inputClasses }}"
            {{ $attributes }}
        >
        
        @if($label)
            <label for="{{ $id }}" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                {{ $label }}
            </label>
        @endif
    </div>
    
    @if($hasError && $errorMessage)
        <p class="mt-2 text-sm text-red-600 dark:text-red-400" role="alert">
            {{ $errorMessage }}
        </p>
    @endif
    
    @if($hint && !$hasError)
        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
            {{ $hint }}
        </p>
    @endif
</div>
