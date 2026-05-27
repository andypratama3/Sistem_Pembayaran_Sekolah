{{-- Reusable Form Textarea Component --}}
@props([
    'name' => '',
    'label' => '',
    'value' => '',
    'placeholder' => '',
    'rows' => 4,
    'required' => false,
    'readonly' => false,
    'disabled' => false,
    'help' => '',
    'maxlength' => '',
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

<div {{ $attributes->merge(['class' => 'row mb-4 align-items-start']) }}>
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
        <textarea
            class="form-control {{ $errorClass }}"
            id="{{ $name }}"
            name="{{ $name }}"
            rows="{{ $rows }}"
            placeholder="{{ $placeholder }}"
            {{ $required ? 'required' : '' }}
            {{ $readonly ? 'readonly' : '' }}
            {{ $disabled ? 'disabled' : '' }}
            {{ $maxlength ? "maxlength=\"{$maxlength}\"" : '' }}
        >{{ $oldValue }}</textarea>

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
