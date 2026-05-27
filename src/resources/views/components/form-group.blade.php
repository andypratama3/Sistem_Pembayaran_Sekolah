@props([
    'label' => null,
    'id' => null,
    'error' => null,
    'required' => false,
    'help' => null,
    'class' => 'mb-3',
])

<div class="{{ $class }}">
    @if($label)
        <label for="{{ $id }}" class="form-label fw-semibold text-uppercase mb-2">
            {{ $label }}
            @if($required) <span class="text-danger">*</span> @endif
        </label>
    @endif

    {{ $slot }}

    @if($help)
        <div class="form-text small text-body-secondary mt-1">{{ $help }}</div>
    @endif

    @if($error)
        <div class="invalid-feedback d-block mt-1">
            <i class="bi bi-exclamation-circle me-1"></i>{{ $error }}
        </div>
    @endif
</div>
