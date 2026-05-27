<!-- Alert Component -->
@php($dismissible ??= false)
<div @class([
    'alert',
    'alert-success' => $type === 'success',
    'alert-danger' => $type === 'error',
    'alert-warning' => $type === 'warning',
    'alert-info' => $type === 'info',
    'alert-dismissible fade show' => $dismissible,
]) role="alert">
    {{ $slot }}
    @if ($dismissible)
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    @endif
</div>
