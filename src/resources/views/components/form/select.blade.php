{{-- Reusable Form Select Component --}}
@props([
    'name' => '',
    'label' => '',
    'options' => [],
    'value' => '',
    'placeholder' => 'Pilih...',
    'required' => false,
    'disabled' => false,
    'multiple' => false,
    'readonly' => false,
    'help' => '',
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
        <select
            id="{{ $name }}"
            name="{{ $multiple ? $name . '[]' : $name }}"
            class="form-control {{ $errorClass }}"
            data-select2-selector="default"
            {{ $required ? 'required' : '' }}
            {{ $disabled ? 'disabled' : '' }}
            {{ $readonly ? 'readonly' : '' }}
            {{ $multiple ? 'multiple' : '' }}
        >
            @if(!$multiple && $placeholder)
                <option value="">{{ $placeholder }}</option>
            @endif

            @foreach($options as $optValue => $optLabel)
                <option
                    value="{{ $optValue }}"
                    {{ ($multiple ? is_array($oldValue) && in_array($optValue, $oldValue) : $oldValue == $optValue) ? 'selected' : '' }}
                >
                    {{ $optLabel }}
                </option>
            @endforeach
        </select>

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
