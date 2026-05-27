@props(['status' => 'pending', 'showIcon' => true])

@php
    $statusConfig = [
        'pending' => [
            'class' => 'badge-warning',
            'label' => 'Menunggu Review',
            'icon' => 'feather-clock',
        ],
        'under_review' => [
            'class' => 'badge-info',
            'label' => 'Sedang Direview',
            'icon' => 'feather-eye',
        ],
        'approved' => [
            'class' => 'badge-success',
            'label' => 'Disetujui',
            'icon' => 'feather-check-circle',
        ],
        'rejected' => [
            'class' => 'badge-danger',
            'label' => 'Ditolak',
            'icon' => 'feather-x-circle',
        ],
        'enrolled' => [
            'class' => 'badge-primary',
            'label' => 'Terdaftar',
            'icon' => 'feather-user-check',
        ],
        'cancelled' => [
            'class' => 'badge-secondary',
            'label' => 'Dibatalkan',
            'icon' => 'feather-x',
        ],
    ];

    $config = $statusConfig[$status] ?? [
        'class' => 'badge-light',
        'label' => ucfirst(str_replace('_', ' ', $status)),
        'icon' => 'feather-help-circle',
    ];
@endphp

<span class="badge {{ $config['class'] }}">
    @if ($showIcon)
        <i class="{{ $config['icon'] }} me-1" style="font-size: 0.8em;"></i>
    @endif
    {{ $config['label'] }}
</span>
