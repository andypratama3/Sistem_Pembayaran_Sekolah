@props([
    'disabled' => false,
    'size' => 'sm',
])

<input 
    @disabled($disabled) 
    {{ $attributes->merge([
        'class' => 'form-control form-control-' . $size . ' border-subtle',
        'placeholder' => $attributes->get('placeholder', '')
    ]) }}
>
