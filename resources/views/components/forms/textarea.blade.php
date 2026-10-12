@props(['name', 'value' => null, 'label' => null, 'placeholder' => null, 'required' => false, 'disabled' => false, 'error' => null, 'hint' => null, 'rows' => 4, 'id' => null])

@php
    $id = $id ?? $name;
    $oldValue = old($name);
    $displayValue = $oldValue !== null ? $oldValue : ($value ?? '');
    $hasError = $error || ($errors && $errors->has($name));
    $errorMessage = $error ?? ($errors->first($name) ?? null);
    
    $inputClasses = 'block w-full rounded-lg border-gray-300 dark:border-gray-600 shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:focus:ring-offset-gray-900 dark:bg-gray-800 dark:text-white';
    $inputClasses .= $hasError ? ' border-red-500 dark:border-red-500' : '';
    $inputClasses .= $disabled ? ' bg-gray-100 dark:bg-gray-700 cursor-not-allowed' : '';
@endphp

<div class="mb-4">
    @if($label)
        <label for="{{ $id }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            {{ $label }}
            @if($required)
                <span class="text-red-500" aria-hidden="true">*</span>
            @endif
        </label>
    @endif
    
    <textarea 
        id="{{ $id }}"
        name="{{ $name }}"
        rows="{{ $rows }}"
        {{ $disabled ? 'disabled' : '' }}
        {{ $required ? 'required' : '' }}
        {{ $placeholder ? 'placeholder=' . $placeholder : '' }}
        class="{{ $inputClasses }}"
        {{ $attributes }}
    >{{ $displayValue }}</textarea>
    
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
