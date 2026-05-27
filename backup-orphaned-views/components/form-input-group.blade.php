@props([
    'label' => null,
    'id' => null,
    'name' => null,
    'icon' => null,
    'required' => false,
    'error' => null,
    'help' => null,
    'class' => 'mb-3 row align-items-center',
    'labelCol' => 'col-lg-4',
    'inputCol' => 'col-lg-8',
])

<div class="{{ $class }}">
    @if($label)
        <div class="{{ $labelCol }}">
            <label for="{{ $id ?? $name }}" class="form-label fw-semibold text-uppercase mb-lg-0">
                {{ $label }}
                @if($required) <span class="text-danger">*</span> @endif
            </label>
        </div>
    @endif
    <div class="{{ $inputCol }}">
        <div class="input-group input-group-sm">
            @if($icon)
                <span class="input-group-text bg-body-secondary border-subtle">
                    <i class="{{ $icon }} text-body-secondary"></i>
                </span>
            @endif

            {{ $slot }}

            @if(isset($action))
                {{ $action }}
            @endif
        </div>

        @if($help)
            <small class="form-text text-body-secondary mt-1 d-block">{{ $help }}</small>
        @endif

        @if($error || ($name && $errors->has($name)))
            <div class="invalid-feedback d-block mt-1">
                <i class="bi bi-exclamation-circle me-1"></i>{{ $error ?? $errors->first($name) }}
            </div>
        @endif
    </div>
</div>
