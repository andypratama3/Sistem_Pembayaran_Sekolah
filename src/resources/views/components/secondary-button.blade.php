<button {{ $attributes->merge(['type' => 'button', 'class' => 'btn btn-rounded btn-secondary']) }}>
    {{ $slot }}
</button>
