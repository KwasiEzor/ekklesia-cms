@props([
    'size' => 'medium'
])

@php
    $paddingClass = match($size) {
        'small' => 'py-3 sm:py-6',
        'medium' => 'py-6 sm:py-12',
        'large' => 'py-12 sm:py-24',
        'xlarge' => 'py-24 sm:py-48',
        default => 'py-6 sm:py-12',
    };
@endphp

<div class="{{ $paddingClass }}"></div>
