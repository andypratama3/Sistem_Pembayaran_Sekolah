@props([
    'title' => null,
    'value' => 0,
    'suffix' => '',
    'prefix' => '',
    'icon' => null,
    'iconBg' => 'bg-soft-primary',
    'iconColor' => 'text-primary',
    'trend' => null,
    'trendLabel' => null,
    'trendUp' => true,
    'id' => null,
    'route' => null,
    'routeLabel' => 'Lihat Detail',
    'badge' => null,
    'badgeClass' => 'bg-soft-success text-success',
    'format' => 'number', // number, currency, percent
    'divisor' => 1, // for dividing numbers (e.g., 1000000 for millions)
    'decimal' => 0, // decimal places
])

@php
    $displayValue = $value;
    if ($format === 'currency' && is_numeric($value)) {
        $displayValue = number_format($value / $divisor, $decimal);
    } elseif (is_numeric($value)) {
        $displayValue = number_format($value);
    }
    
    if ($format === 'percent') {
        $suffix = '%' . $suffix;
    }
@endphp

<div class="col-xxl-3 col-sm-6">
    <x-card full-height class="border-0 shadow-sm premium-card">
        <div class="d-flex align-items-center justify-content-between">
            <div class="gap-3 d-flex align-items-center">
                @if($icon)
                <div class="rounded-circle avatar-text avatar-xl {{ $iconBg }} {{ $iconColor }}">
                    <i class="feather-{{ $icon }}"></i>
                </div>
                @endif
                <div>
                    @if($title)
                        <span class="text-muted small fw-bold text-uppercase d-block">{{ $title }}</span>
                    @endif
                    <span class="fs-28 fw-bolder d-block" id="{{ $id }}">
                        {{ $prefix }}{{ $displayValue }}{{ $suffix }}
                    </span>
                </div>
            </div>
            @if($trend || $badge)
                <div class="{{ $badgeClass }}">
                    @if($trend)
                        <i class="feather-{{ $trendUp ? 'trending-up' : 'trending-down' }} fs-10 me-1"></i>
                        {{ $trend }}
                    @else
                        {{ $badge }}
                    @endif
                </div>
            @endif
        </div>
        @if($route)
            <div class="mt-3">
                <a href="{{ $route }}" class="text-primary fw-bold small">{!! $routeLabel !!} <i class="feather-arrow-right fs-10"></i></a>
            </div>
        @endif
    </x-card>
</div>