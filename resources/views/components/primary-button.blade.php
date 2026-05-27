@props([
    'type' => 'submit',
    'size' => 'sm',
])

<button {{ $attributes->merge([
    'type' => $type,
    'class' => 'btn btn-primary btn-' . $size
]) }}>
    {{ $slot }}
</button>
