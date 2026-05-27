"use strict";

window.__useEnhancedDataTables = true;

// ── Config ────────────────────────────────────────────────────────────────────
const DT_CONFIG = {
    destroy: true,
    pageLength: 100,
    lengthMenu: [10, 20, 50, 100, 200, 500],
    processing: true,
    serverSide: true,
    searchDelay: 500,
    autoWidth: false,
    dom: "<'row align-items-center mb-3'<'col-sm-6'l><'col-sm-6'>>rt<'row mt-3'<'col-sm-6'i><'col-sm-6 d-flex justify-content-end'p>>",
};

const DT_TYPING_DEBOUNCE_MS = 450;
const dtDebounceTimers = {};

function debounceByTable(tableId, callback, delay = DT_TYPING_DEBOUNCE_MS) {
    if (!tableId) return;
    clearTimeout(dtDebounceTimers[tableId]);
    dtDebounceTimers[tableId] = setTimeout(callback, delay);
}

function applyTableSearch(tableId, keyword) {
    if (!tableId || !$.fn.DataTable.isDataTable(tableId)) return;

    const table = $(tableId).DataTable();
    const nextKeyword = String(keyword || '');
    if (table.search() === nextKeyword) return;

    table.search(nextKeyword).draw();
}

// ── 1. Helper: Get all checked row IDs ──────────────────────────────────────
function getCheckedIds(tableId) {
    return $(tableId + ' tbody .checkbox:checked')
        .map(function() {
            return $(this).closest('tr').data('id') || $(this).val();
        })
        .get();
}

// ── 2. Helper: Reload table without page refresh ───────────────────────────
function reloadTable(tableId) {
    const table = $(tableId).DataTable();
    if (table) {
        table.ajax.reload(null, false);
    }
}

// ── 3. Helper: Clear and reload table ──────────────────────────────────────
function clearAndReloadTable(tableId) {
    const table = $(tableId).DataTable();
    if (table) {
        table.clear().draw();
        table.ajax.reload();
    }
}

// ── Resolve filter form for a given table ────────────────────────────────────
// Priority: table[data-filter-form] -> local #filterForm in same card -> global #filterForm
function getFilterFormForTable(tableId) {
    const $table = $(tableId);
    const explicitSelector = $table.attr('data-filter-form') || $table.data('filterForm');

    if (explicitSelector) {
        const $explicitForm = $(explicitSelector).first();
        if ($explicitForm.length) return $explicitForm;
    }

    const $localForm = $table.closest('.card').find('#filterForm').first();
    if ($localForm.length) return $localForm;

    return $('#filterForm').first();
}

// ── Resolve table id for a given filter input/button element ─────────────────
function getTableIdFromFilterElement(element) {
    const $form = $(element).closest('#filterForm');
    if (!$form.length) return null;

    const target = $form.attr('data-table-target') || $form.data('tableTarget');
    if (target && $(target).length) {
        return target;
    }

    const $table = $form.closest('.card').find('table').first();
    if ($table.length && $table.attr('id')) {
        return '#' + $table.attr('id');
    }

    return null;
}

// ── Resolve table id for action buttons (delete/edit) ────────────────────────
function getTableIdFromActionElement(element) {
    const $el = $(element);

    const explicitTarget = $el.attr('data-table-target') || $el.data('tableTarget') || $el.attr('data-table-id') || $el.data('tableId');
    if (explicitTarget) {
        const normalizedTarget = String(explicitTarget).startsWith('#') ? String(explicitTarget) : '#' + String(explicitTarget);
        if ($(normalizedTarget).length) return normalizedTarget;
    }

    const $closestTable = $el.closest('table[id]');
    if ($closestTable.length) {
        return '#' + $closestTable.attr('id');
    }

    const $closestCardTable = $el.closest('.card').find('table[id]').first();
    if ($closestCardTable.length) {
        return '#' + $closestCardTable.attr('id');
    }

    const $singleKnownTable = $('table.items-wrapper[id]');
    if ($singleKnownTable.length === 1) {
        return '#' + $singleKnownTable.first().attr('id');
    }

    return null;
}

// ── 4. Helper: Initialize server-side DataTable ────────────────────────────
function initDataTable(tableId, ajaxUrl, options = {}) {
    let columns = [];

    if (options.columns && options.columns.length > 0) {
        // ✅ Pakai columns dari options jika sudah disupply (dari component)
        columns = options.columns;
    } else {
        // ✅ Fallback: auto-detect dari thead
        $(tableId + ' thead tr th').each(function(index, el) {
            const $th = $(el);
            const text = $th.text().trim();

            if ($th.find('input[type="checkbox"]').length > 0 || (index === 0 && text === '')) {
                columns.push({ data: 'checkbox', name: 'checkbox', orderable: false, searchable: false });
                return;
            }

            if ($th.hasClass('text-end') || text.toLowerCase() === 'actions') {
                columns.push({ data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-end' });
                return;
            }

            var key = text
                .replace(/\(.*?\)/g, '')
                .trim()
                .toLowerCase()
                .replace(/\s+/g, '_');

            columns.push({
                data: key,
                name: key,
                orderable: !$th.hasClass('no-sort'),
                searchable: !$th.hasClass('no-search'),
                render: function(data, type, row) {
                    return data !== null && data !== undefined ? data : '<span class="text-muted">-</span>';
                }
            });
        });
    }

    // ── AJAX Configuration ───────────────────────────────────────────────────
    let ajaxConfig = {
        url: typeof ajaxUrl === 'string' ? ajaxUrl : (ajaxUrl.url || ''),
        data: function(d) {
            // Append custom filter form data
            // Skip input name="search" — itu sudah dihandle DT native via search[value]
            const filterForm = getFilterFormForTable(tableId);
            if (filterForm.length) {
                filterForm.find('input, select, textarea').each(function() {
                    const name = $(this).attr('name');
                    const type = $(this).attr('type');

                    if (!name) return;
                    if (name === 'search') return; // ✅ Skip, DT handle sendiri

                    if (type === 'checkbox' || type === 'radio') {
                        if ($(this).is(':checked')) d[name] = $(this).val();
                    } else {
                        d[name] = $(this).val();
                    }
                });
            }

            if (typeof ajaxUrl === 'object' && typeof ajaxUrl.data === 'function') {
                ajaxUrl.data(d);
            }
        },
        error: function(xhr, error, thrown) {
            console.error('AJAX error:', error);
            handleDataTableError(tableId, error);
        }
    };

    if (typeof ajaxUrl === 'object') {
        const { data, url, ...rest } = ajaxUrl;
        ajaxConfig = { ...ajaxConfig, ...rest };
    }

    // ✅ Pisahkan columns dari options agar tidak overwrite dua kali
    const { columns: _ignoredColumns, ...restOptions } = options;

    const config = {
        ...DT_CONFIG,
        ajax: ajaxConfig,
        columns: columns,
        columnDefs: [
            {
                targets: 0,
                orderable: false,
                searchable: false,
                className: 'text-center',
                render: function(data, type, row) {
                    try {
                        const id = row.id || '';
                        return `
                            <div class="custom-control custom-checkbox ms-1">
                                <input type="checkbox" class="custom-control-input checkbox" value="${id}" data-id="${id}" id="chk_${id}">
                                <label class="custom-control-label" for="chk_${id}"></label>
                            </div>`;
                    } catch (error) {
                        console.error('Checkbox render error:', error);
                        return '<span class="text-muted">-</span>';
                    }
                }
            },
            {
                targets: -1,
                orderable: false,
                searchable: false,
                className: 'text-end',
                render: function(data, type, row) {
                    return data || '<span class="text-muted">-</span>';
                }
            }
        ],
        ...restOptions, // ✅ Spread tanpa columns (sudah di-handle di atas)
    };

    const table = $(tableId).DataTable(config);
    $(tableId).data('dataTable', table);

    // Apply initial search value from filter form (e.g. from URL query params)
    const filterForm = getFilterFormForTable(tableId);
    if (filterForm.length) {
        const searchInput = filterForm.find('input[name="search"]');
        if (searchInput.length && searchInput.val()) {
            table.search(searchInput.val()).draw();
        }
    }

    return table;
}

// ── 5. Helper: Initialize "Select All" checkbox ────────────────────────────
function initCheckAll(tableId) {
    const $table = $(tableId);
    const $card = $table.closest('.card');
    const $checkAll = $card.find('[id^="checkAll"]');
    const $toolbar = $card.find('.bulk-toolbar');
    const $count = $toolbar.find('.bulk-count');

    if (!$checkAll.length) {
        console.warn(`No "checkAll" checkbox found for ${tableId}`);
        return;
    }

    function syncToolbar() {
        const checkedIds = getCheckedIds(tableId);
        const count = checkedIds.length;
        const totalRows = $table.find('tbody tr').length;

        if (count > 0) {
            $count.text(`${count} row${count > 1 ? 's' : ''} selected`);
            $toolbar.removeClass('d-none');
        } else {
            $toolbar.addClass('d-none');
            $checkAll.prop('checked', false);
        }

        $checkAll.prop('checked', totalRows > 0 && count === totalRows);
    }

    $checkAll.on('change', function() {
        const isChecked = this.checked;
        $table.find('tbody .checkbox').each(function() {
            this.checked = isChecked;
            $(this).closest('tr').toggleClass('table-active selected', isChecked);
        });
        syncToolbar();
    });

    $(document).on('change', tableId + ' .checkbox', function() {
        $(this).closest('tr').toggleClass('table-active selected', this.checked);
        syncToolbar();
    });

    $table.on('draw.dt', function() {
        syncToolbar();
    });
}

// ── 6. Helper: Setup real-time sync via Laravel Echo ───────────────────────
function initRealtimeSync(tableId, modelName) {
    if (typeof Echo === 'undefined' || typeof Echo.channel !== 'function') {
        return;
    }

    Echo.channel('data-updated').listen('.data.updated', (event) => {
        if (event.model === modelName) {
            console.log(`[REALTIME] ${modelName} ${event.action}, refreshing table...`);
            reloadTable(tableId);
        }
    });
}

// ── 7. Helper: Handle DataTable errors gracefully ──────────────────────────
function handleDataTableError(tableId, error) {
    console.error(`DataTable Error (${tableId}):`, error);
    const $tbody = $(tableId).find('tbody');

    $tbody.html(`
        <tr>
            <td colspan="100%" class="text-center text-danger py-5">
                <i class="feather-alert-circle me-2"></i>
                Error loading data. Please refresh the page.
            </td>
        </tr>
    `);
}

// ── 8. Helper: Export table to CSV ────────────────────────────────────────
function exportTableToCSV(tableId, filename = 'export.csv') {
    const table = $(tableId).DataTable();
    const data = table.rows({ search: 'applied' }).data();

    let csv = '';
    table.columns().every(function() {
        csv += '"' + this.header().textContent + '",';
    });
    csv += '\n';

    data.each(function(row) {
        Object.values(row).forEach(val => {
            csv += '"' + (val || '') + '",';
        });
        csv += '\n';
    });

    const blob = new Blob([csv], { type: 'text/csv' });
    const link = document.createElement('a');
    link.href = window.URL.createObjectURL(blob);
    link.download = filename;
    link.click();
}

// ── 9. Helper: Refresh single row ─────────────────────────────────────────
function refreshRow(tableId, rowId) {
    const table = $(tableId).DataTable();
    const row = table.rows((idx, data) => data.id === rowId);
    table.rows(row[0]).invalidate().draw(false);
}

// ── 10. Helper: Perform Bulk Action (delete/export) ──────────────────────
function performBulkAction(tableId, action, modelName) {
    const ids = getCheckedIds(tableId);
    if (ids.length === 0) {
        Swal.fire('Peringatan', 'Pilih minimal 1 data terlebih dahulu.', 'warning');
        return;
    }

    const actionLabel = action === 'delete' ? 'menghapus' : 'mengexport';
    const confirmColor = action === 'delete' ? '#ef4444' : '#3b82f6';

    window.ConfirmAction(
        `Konfirmasi ${action === 'delete' ? 'Hapus' : 'Export'}`,
        `Apakah Anda yakin ingin ${actionLabel} ${ids.length} data?`,
        `Ya, ${action === 'delete' ? 'Hapus' : 'Export'}!`
    ).then((result) => {
        if (result.value) {
            $.ajax({
                url: `/dashboard/bulk-operations/${action}`,
                method: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    model: modelName,
                    ids: ids
                },
                success: function(response) {
                    if (action === 'export') {
                        window.location.href = `/dashboard/bulk-operations/export?model=${modelName}&` + $.param({ ids: ids });
                        return;
                    }
                    if (window.Toast) {
                        window.Toast.fire('Berhasil!', response.message || 'Aksi berhasil dilakukan', 'success');
                    }
                    reloadTable(tableId);
                },
                error: function(xhr) {
                    const msg = (xhr.responseJSON ? xhr.responseJSON.message : null) || 'Terjadi kesalahan.';
                    Swal.fire('Gagal!', msg, 'error');
                }
            });
        }
    });
}

// ── 11. Document Ready ────────────────────────────────────────────────────
$(document).ready(function() {

    // ── Global Delete Handler ────────────────────────────────────────────
    $(document).on('click', '.delete-btn', function(e) {
        e.preventDefault();
        const url = $(this).data('url');
        const tableId = getTableIdFromActionElement(this);

        if (!url) {
            Swal.fire('Gagal!', 'URL delete tidak ditemukan.', 'error');
            return;
        }

        window.ConfirmAction(
            'Hapus Data?',
            'Data yang dihapus tidak dapat dikembalikan',
            'Ya, Hapus!'
        ).then((result) => {
            if (result.value) {
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: {
                        _method: 'DELETE',
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (window.Toast) {
                            window.Toast.fire({
                                icon: 'success',
                                title: response.message || 'Data berhasil dihapus'
                            });
                        }
                        if (tableId && $(tableId).length) {
                            reloadTable(tableId);
                        } else {
                            setTimeout(() => location.reload(), 1500);
                        }
                    },
                    error: function(xhr) {
                        const msg = (xhr.responseJSON ? xhr.responseJSON.message : null) || xhr.responseText || 'Terjadi kesalahan saat menghapus data';
                        Swal.fire('Gagal!', msg, 'error');
                    }
                });
            }
        });
    });

    // ── Open search modal with Ctrl+K ────────────────────────────────────
    $(document).on('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            const modal = new bootstrap.Modal(document.getElementById('searchModal'));
            modal.show();
        }
    });

    $('#searchInput').on('keyup', function() {
        const query = $(this).val();
        const resultsContainer = $('#searchResults');
        if (query.length < 2) {
            resultsContainer.addClass('d-none');
            return;
        }
        resultsContainer.removeClass('d-none');
    });

    // ── Global Filter Form Listeners ─────────────────────────────────────
    // Select & textarea → langsung reload
    $(document).on('change', '#filterForm select, #filterForm textarea', function() {
        const tableId = getTableIdFromFilterElement(this);
        if (tableId) reloadTable(tableId);
    });

    // Real-time search on input (keyup/input) — fires as user types
    $(document).on('input', '#filterForm input[name="search"]', function() {
        const tableId = getTableIdFromFilterElement(this);
        if (!tableId) return;
        const keyword = $(this).val();
        debounceByTable(tableId, function() {
            applyTableSearch(tableId, keyword);
        });
    });

    // Input (non-search) → reload on change (blur/enter) or keyup with debounce
    $(document).on('change keyup', '#filterForm input:not([name="search"])', function(e) {
        const tableId = getTableIdFromFilterElement(this);
        if (!tableId) return;

        const type = ($(this).attr('type') || '').toLowerCase();
        const isTypingEvent = e.type === 'keyup' && type !== 'checkbox' && type !== 'radio';

        if (isTypingEvent) {
            debounceByTable(tableId, function() {
                reloadTable(tableId);
            });
            return;
        }

        reloadTable(tableId);
    });

    // Select2
    $(document).on('select2:select select2:clear', '#filterForm select', function() {
        const tableId = getTableIdFromFilterElement(this);
        if (tableId) reloadTable(tableId);
    });

    // Reset filter
    $(document).on('click', '#resetFilter, .reset-filter', function(e) {
        e.preventDefault();
        const btn = $(this);
        let tableId = btn.data('table-target');
        
        const form = btn.closest('#filterForm');
        if (form.length) {
            form[0].reset();
            form.find('select').val('').trigger('change.select2');
            form.find('input[type="text"]').val('');
            if (!tableId) tableId = getTableIdFromFilterElement(form);
        }

        if (tableId) {
            // Clear unified component search as well
            $(`[data-table-search="${tableId}"]`).val('');
            reloadTable(tableId);
        }
    });
});
