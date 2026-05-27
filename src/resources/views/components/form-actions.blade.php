@props([
    'cancelRoute',
    'cancelLabel' => 'Batal',
    'submitLabel' => 'Simpan',
    'wrapperClass' => 'd-flex justify-content-between mt-4 px-0',
])

<div class="{{ $wrapperClass }}">
    <div class="page-header-left d-flex align-items-center">
        <a href="{{ route($cancelRoute) }}" class="btn btn-light-brand">
            <i class="feather-arrow-left me-2"></i>
            <span>{{ $cancelLabel }}</span>
        </a>
    </div>
    <div class="page-header-right ms-auto">
        <button type="submit" class="btn btn-primary">
            <i class="feather-save me-2"></i>
            <span>{{ $submitLabel }}</span>
        </button>
    </div>
</div>
