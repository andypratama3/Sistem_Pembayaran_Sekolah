{{-- Reusable Form File Upload Component --}}
@props([
    'name' => '',
    'label' => '',
    'accept' => '',
    'required' => false,
    'help' => '',
    'preview' => false,
    'previewUrl' => '',
    'previewHeight' => '100',
    'previewWidth' => '100',
    'model' => null,
])

@php
$hasError = $errors->has($name);
$errorClass = $hasError ? 'is-invalid' : '';
$currentFile = null;
if (is_object($model) && (method_exists($model, 'getAttribute') || property_exists($model, $name))) {
    $currentFile = $model?->{$name};
}
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
        @if($preview && ($previewUrl || $currentFile))
            <div class="mb-3">
                <img
                    src="{{ $previewUrl ?: \Illuminate\Support\Facades\Storage::url($currentFile) }}"
                    alt="Current {{ $label }}"
                    class="p-1 border rounded"
                    style="height: {{ $previewHeight }}px; width: {{ $previewWidth }}px; object-fit: cover;"
                >
            </div>
        @endif

        <input
            type="file"
            class="form-control {{ $errorClass }}"
            id="{{ $name }}"
            name="{{ $name }}"
            {{ $accept ? "accept=\"{$accept}\"" : '' }}
            {{ $required ? 'required' : '' }}
        >

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
