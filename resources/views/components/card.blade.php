@props([
    'class' => '',
    'title' => null,
    'header' => null,
    'footer' => null,
    'fullHeight' => false,
])

<div {{ $attributes->merge(['class' => 'card ' . ($fullHeight ? 'stretch stretch-full ' : '') . $class]) }}>
    @if ($title || $header)
        <div class="p-4 card-header border-bottom">
            @if ($title)
                <h5 class="mb-0 card-title">{{ $title }}</h5>
            @else
                {{ $header }}
            @endif
        </div>
    @endif

    <div class="p-4 card-body">
        {{ $slot }}
    </div>

    @if ($footer)
        <div class="p-4 card-footer border-top bg-body-secondary">
            {{ $footer }}
        </div>
    @endif
</div>
