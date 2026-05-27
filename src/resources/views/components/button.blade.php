@props([
    'variant' => 'primary',
    'size' => 'sm',
    'type' => 'button',
])

<button 
    type="{{ $type }}" 
    {{ $attributes->merge([
        'class' => 'btn btn-' . $variant . ' btn-' . $size
    ]) }}
>
    {{ $slot }}
</button>
