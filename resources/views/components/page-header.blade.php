@props([
    'createRoute' => null,
    'createRouteParams' => null,
    'createAbility' => null,
    'createModel' => null,
    'title' => null,
    'left' => null,
    'right' => null,
])

<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        @if ($title)
            <div class="page-header-title">
                <h5 class="m-b-10">{{ $title }}</h5>
            </div>
        @endif
        @if ($left)
            {{ $left }}
        @else
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard.index') }}">Home</a></li>
                <li class="breadcrumb-item active">{{ $title ?? '' }}</li>
            </ul>
        @endif
    </div>

    <div class="page-header-right ms-auto">
        <div class="d-flex align-items-center gap-2">

            {{-- SLOT: filter / custom actions --}}
            {{ $actions ?? '' }}

            {{-- CREATE BUTTON --}}
            @php
                $createUrl = null;
                if ($createRoute) {
                    try {
                        $params = $createRouteParams ?? [];
                        if (!is_array($params)) {
                            $params = [$params];
                        }
                        $createUrl = route($createRoute, $params);
                    } catch (\Throwable $e) {
                        $createUrl = null;
                    }
                }
            @endphp
            @if ($createUrl)
                @if ($createAbility)
                    @can($createAbility, $createModel)
                        <a href="{{ $createUrl }}" class="btn btn-md btn-primary">
                            <i class="feather-plus me-2"></i>
                            <span>Create New</span>
                        </a>
                    @endcan
                @else
                    <a href="{{ $createUrl }}" class="btn btn-md btn-primary">
                        <i class="feather-plus me-2"></i>
                        <span>Create New</span>
                    </a>
                @endif
            @endif

        </div>
    </div>
</div>
