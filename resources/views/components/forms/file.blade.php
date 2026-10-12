@props(['name', 'label' => null, 'accept' => '*', 'required' => false, 'disabled' => false, 'error' => null, 'hint' => null, 'id' => null])

@php
    $id = $id ?? $name;
    $hasError = $error || ($errors && $errors->has($name));
    $errorMessage = $error ?? ($errors->first($name) ?? null);
    
    $inputClasses = 'block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-gray-100 dark:file:bg-gray-700 file:text-gray-700 dark:file:text-gray-300 hover:file:bg-gray-200 dark:hover:file:bg-gray-600';
    $inputClasses .= $hasError ? ' border-red-500 dark:border-red-500' : '';
    $inputClasses .= $disabled ? ' cursor-not-allowed opacity-50' : '';
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
    
    <input 
        type="file"
        id="{{ $id }}"
        name="{{ $name }}"
        accept="{{ $accept }}"
        {{ $disabled ? 'disabled' : '' }}
        {{ $required ? 'required' : '' }}
        class="{{ $inputClasses }}"
        {{ $attributes }}
    >
    
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
