@props([
    'id' => 'dataTable',
    'title' => 'Data Table',
    'columns' => [],
    'actionRoute' => null,
    'apiRoute' => null,
    'model' => null,
    'canCreate' => true,
    'canEdit' => true,
    'canDelete' => true,
    'bulkActions' => [],
    'searchPlaceholder' => 'Search...',
])

@php
    $ajaxUrl = $apiRoute ?? null;
    if (!$ajaxUrl && $actionRoute) {
        $routeName = str_starts_with($actionRoute, 'dashboard.')
            ? $actionRoute . '.index'
            : 'dashboard.' . $actionRoute . '.index';
        $ajaxUrl = route($routeName);
    }
@endphp
<style>
    .select2-selection select2-selection--single {
        width: 100% !important;
    }
</style>

<div class="pb-3 card-header d-flex justify-content-between align-items-center">
    <h5 class="mb-0 card-title">{{ $title ?? 'Data Table' }}</h5>
    <div class="gap-2 custom-card-action d-flex align-items-center">
        @if(!isset($hasManualSearch) || !$hasManualSearch)
        <div class="gap-2 table-search-box d-flex align-items-center">
            <div class="input-group input-group-sm">
                <span class="border-0 input-group-text pe-0"><i class="feather-search"></i></span>
                <input type="text" class="form-control form-control-sm"
                    data-table-search="#{{ $id }}" placeholder="{{ $searchPlaceholder }}">
            </div>
            @if(!isset($hasManualReset) || !$hasManualReset)
            <button type="button" class="btn btn-sm btn-danger reset-filter" data-table-target="#{{ $id }}" title="Reset Table">
                <i class="feather-refresh-cw"></i>
            </button>
            @endif
        </div>
        @endif
    </div>
</div>

<div class="p-0 card-body">

    @if (!empty($bulkActions))
        <div class="px-4 py-3 border-bottom d-none" data-bulk-toolbar>
            <div class="flex-wrap gap-2 d-flex align-items-center justify-content-between">
                <span class="text-muted small fw-semibold" data-bulk-count></span>
                <div class="shadow-sm btn-group" role="group">
                    @forelse ($bulkActions as $actionKey => $actionValue)
                        @php
                            $actionName = is_array($actionValue) ? $actionValue['name'] ?? $actionKey : $actionKey;
                            $actionLabel = is_array($actionValue)
                                ? $actionValue['label'] ?? $actionValue
                                : $actionValue;
                            $actionColor = is_array($actionValue) ? $actionValue['color'] ?? 'primary' : 'primary';
                            $actionIcon = is_array($actionValue)
                                ? $actionValue['icon'] ?? ($actionName === 'delete' ? 'feather-trash-2' : null)
                                : ($actionKey === 'delete'
                                    ? 'feather-trash-2'
                                    : null);
                        @endphp
                        <button type="button" class="btn btn-sm btn-outline-{{ $actionColor }}"
                            data-bulk-action="{{ $actionName }}"
                            @if ($actionName === 'delete') data-bulk-delete="true" @endif>
                            @if ($actionIcon)
                                <i class="{{ $actionIcon }} me-1"></i>
                            @endif
                            {{ $actionLabel }}
                        </button>
                    @empty
                    @endforelse
                </div>
            </div>
        </div>
    @endif

    {{-- Table --}}
    <div class="table-responsive">
        <table id="{{ $id }}" class="table mb-0 table-hover items-wrapper border-subtle" style="width:100%">
            <thead class="bg-body-secondary">
                <tr>
                    <th width="40" class="border-subtle">
                        <div class="custom-control custom-checkbox ms-1">
                            <input type="checkbox" class="custom-control-input" id="checkAll">
                            <label class="custom-control-label" for="checkAll"></label>
                        </div>
                    </th>
                    @foreach ($columns as $column)
                        <th class="border-subtle">{{ $column['title'] ?? ($column['label'] ?? '') }}</th>
                    @endforeach
                    <th class="text-end border-subtle" width="120">Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr class="empty-state-row" style="display: none;">
                    <td colspan="{{ count($columns) + 2 }}" class="py-5 text-center">
                        <div class="d-flex flex-column align-items-center justify-content-center">
                            <i class="mb-3 feather-inbox text-muted" style="font-size: 3rem; opacity: 0.5;"></i>
                            <h5 class="mb-2 text-muted">Tidak ada data</h5>
                            <p class="mb-0 text-muted small">Belum ada data yang tersedia untuk ditampilkan</p>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
    <script>
        $(document).ready(function() {
            const tableId = '#{{ $id }}';

            function initSelect2InTable() {
                if (!$.fn.select2) {
                    return;
                }

                $(tableId).find('select[data-select2-selector]').each(function() {
                    const $select = $(this);

                    if ($select.hasClass('select2-hidden-accessible')) {
                        return;
                    }

                    const selectorType = $select.data('select2-selector');
                    const select2Config = {
                        theme: 'bootstrap-5',
                        width: '100%'
                    };

                    if (
                        ['tag', 'status', 'priority', 'label', 'type'].includes(selectorType) &&
                        typeof window.bgformat === 'function'
                    ) {
                        select2Config.templateResult = window.bgformat;
                        select2Config.templateSelection = window.bgformat;
                    }

                    const $dropdownParent = $select.closest('td, .modal, .offcanvas, .card-body');
                    if ($dropdownParent.length) {
                        select2Config.dropdownParent = $dropdownParent.first();
                    }

                    $select.select2(select2Config);
                });
            }

            // ✅ Build columns dengan name & searchable yang benar
            const dtColumns = [{
                    data: 'checkbox',
                    name: 'checkbox',
                    orderable: false,
                    searchable: false,
                },
                @foreach ($columns as $column)
                    {
                        data: '{{ $column['data'] ?? ($column['key'] ?? '') }}',
                        name: '{{ $column['name'] ?? ($column['data'] ?? ($column['key'] ?? '')) }}',
                        searchable: {{ isset($column['searchable']) ? ($column['searchable'] ? 'true' : 'false') : 'true' }},
                        orderable: {{ isset($column['orderable']) ? ($column['orderable'] ? 'true' : 'false') : 'true' }},
                    }
                    @if (!$loop->last)
                        ,
                    @endif
                @endforeach
            ];

            // Add action column manually after columns
            const allColumns = [...dtColumns, {
                data: 'action',
                name: 'action',
                orderable: false,
                searchable: false,
                className: 'text-end',
            }];

            // Initialize DataTable — columns disupply dari sini, bukan auto-detect
            const table = initDataTable(tableId, '{{ $ajaxUrl }}', {
                columns: allColumns,
                language: {
                    search: '{{ $searchPlaceholder }}',
                    searchPlaceholder: '{{ $searchPlaceholder }}',
                },
            });

            table.on('draw.dt', function() {
                initSelect2InTable();
            });

            // ✅ Handle internal search input
            $(`[data-table-search="${tableId}"]`).on('input', function() {
                const keyword = $(this).val();
                debounceByTable(tableId, function() {
                    applyTableSearch(tableId, keyword);
                });
            });

            initSelect2InTable();

            // Initialize checkboxes & bulk toolbar
            initCheckAll(tableId);

            // Wire bulk action buttons
            @if (!empty($bulkActions) && $model)
                $(tableId).closest('.card').on('click', '[data-bulk-action], [data-bulk-delete]', function() {
                    const action = $(this).data('bulk-action') || 'delete';
                    performBulkAction(tableId, action, '{{ $model }}');
                });
            @endif

            // Realtime sync
            @if ($model)
                initRealtimeSync(tableId, '{{ $model }}');
            @endif
        });
    </script>
@endpush
