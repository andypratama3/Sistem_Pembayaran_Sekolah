{{-- Reusable Form Input Component --}}
@props([
    'name' => '',
    'label' => '',
    'type' => 'text',
    'value' => '',
    'placeholder' => '',
    'icon' => '',
    'required' => false,
    'readonly' => false,
    'disabled' => false,
    'help' => '',
    'maxlength' => '',
    'minlength' => '',
    'pattern' => '',
    'model' => null,
])

@php
$modelValue = null;
if (is_object($model) && (method_exists($model, 'getAttribute') || property_exists($model, $name))) {
    $modelValue = $model?->{$name};
}
$oldValue = old($name, $value ?? $modelValue ?? '');
$hasError = $errors->has($name);
$errorClass = $hasError ? 'is-invalid' : '';
@endphp

<div {{ $attributes->merge(['class' => 'row mb-4 align-items-center']) }}>
    @if($label)
        <div class="col-lg-4">
            <label for="{{ $name }}" class="fw-semibold text-dark">
                {{ $label }}
                @if($required)
                    <span class="text-danger">*</span>
                @endif
            </label>
        </div>
    @endif

    <div class="{{ $label ? 'col-lg-8' : 'col-12' }}">
        <div class="input-group">
            @if($icon)
                <span class="input-group-text">
                    <i class="feather-{{ $icon }}"></i>
                </span>
            @endif

            <input
                type="{{ $type }}"
                class="form-control {{ $errorClass }}"
                id="{{ $name }}"
                name="{{ $name }}"
                value="{{ $oldValue }}"
                placeholder="{{ $placeholder }}"
                {{ $required ? 'required' : '' }}
                {{ $readonly ? 'readonly' : '' }}
                {{ $disabled ? 'disabled' : '' }}
                {{ $maxlength ? "maxlength=\"{$maxlength}\"" : '' }}
                {{ $minlength ? "minlength=\"{$minlength}\"" : '' }}
                {{ $pattern ? "pattern=\"{$pattern}\"" : '' }}
            >
        </div>

        @if($hasError)
            <div class="invalid-feedback d-block">
                {{ $errors->first($name) }}
            </div>
        @endif

        @if($help)
            <small class="form-text text-muted d-block mt-2">{{ $help }}</small>
        @endif
    </div>
</div>
