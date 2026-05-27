<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        {{-- <div class="page-header-title">
            <h5 class="m-b-10">
                {{ Breadcrumbs::current()?->title ?? '' }}
            </h5>
        </div>

        <ul class="breadcrumb">
            @foreach (Breadcrumbs::generate() as $breadcrumb)
                @if ($breadcrumb->url && ! $loop->last)
                    <li class="breadcrumb-item">
                        <a href="{{ $breadcrumb->url }}">
                            {{ $breadcrumb->title }}
                        </a>
                    </li>
                @else
                    <li class="breadcrumb-item active">
                        {{ $breadcrumb->title }}
                    </li>
                @endif
            @endforeach
        </ul> --}}
    </div>

    {{-- RIGHT SIDE (UNCHANGED) --}}
    <div class="page-header-right ms-auto">
        <div class="page-header-right-items">
            <div class="d-flex d-md-none">
                <a href="javascript:void(0)" class="page-header-right-close-toggle">
                    <i class="feather-arrow-left me-2"></i>
                    <span>Back</span>
                </a>
            </div>

            <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                <div id="reportrange" class="reportrange-picker d-flex align-items-center">
                    <span class="reportrange-picker-field"></span>
                </div>

                <div class="dropdown filter-dropdown">
                    <a class="btn btn-md btn-light-brand"
                       data-bs-toggle="dropdown"
                       data-bs-offset="0, 10"
                       data-bs-auto-close="outside">
                        <i class="feather-filter me-2"></i>
                        <span>Filter</span>
                    </a>

                    <div class="dropdown-menu dropdown-menu-end">
                        @foreach (['Role','Team','Email','Member','Recommendation'] as $item)
                            <div class="dropdown-item">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox"
                                           class="custom-control-input"
                                           id="{{ $item }}"
                                           checked>
                                    <label class="custom-control-label c-pointer"
                                           for="{{ $item }}">
                                        {{ $item }}
                                    </label>
                                </div>
                            </div>
                        @endforeach

                        <div class="dropdown-divider"></div>

                        <a href="javascript:void(0);" class="dropdown-item">
                            <i class="feather-plus me-3"></i>
                            <span>Create New</span>
                        </a>

                        <a href="javascript:void(0);" class="dropdown-item">
                            <i class="feather-filter me-3"></i>
                            <span>Manage Filter</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-md-none d-flex align-items-center">
            <a href="javascript:void(0)" class="page-header-right-open-toggle">
                <i class="feather-align-right fs-20"></i>
            </a>
        </div>
    </div>
</div>