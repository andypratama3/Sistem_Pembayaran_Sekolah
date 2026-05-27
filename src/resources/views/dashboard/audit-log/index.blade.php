@extends('layouts.app')

@section('title', 'Audit Log')

@section('page-header')
    <x-page-header title="Log Aktivitas Sistem">
        <x-slot:left>
            {{ Breadcrumbs::render('dashboard.audit_log.index') }}
        </x-slot:left>
    </x-page-header>
@endsection

@section('content')
    <div class="col-lg-12">
        <div class="card stretch stretch-full">
            @include('components.swal-flash')

            <div id="filterForm" data-table-target="#auditLogsTable" class="m-3 row g-3">
                <h5>Filter Aktivitas</h5>

                <div class="col-md-3">
                    <label class="form-label small fw-bold text-uppercase">Tanggal Mulai</label>
                    <input type="text" name="date_from" data-datepicker="true" class="form-control" placeholder="YYYY-MM-DD">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-uppercase">Tanggal Akhir</label>
                    <input type="text" name="date_to" data-datepicker="true" class="form-control" placeholder="YYYY-MM-DD">
                </div>
            </div>

            <x-data-table id="auditLogsTable" title="Log Aktivitas User & Sistem" :columns="[
                ['data' => 'user_name', 'title' => 'Pengguna'],
                ['data' => 'desc', 'title' => 'Aksi'],
                ['data' => 'model_type', 'title' => 'Tipe Model'],
                ['data' => 'model_id', 'title' => 'ID Model'],
                ['data' => 'description', 'title' => 'Deskripsi'],
                ['data' => 'ip_address', 'title' => 'Alamat IP'],
                ['data' => 'created_at', 'title' => 'Waktu Kejadian'],
            ]" action-route="audit_log" :api-route="route('dashboard.audit_log.datatable')" model="AuditLog"
                :can-create="false" :can-edit="false"
                :can-delete="auth()->user()->can('delete', App\Models\AuditLog::class)"
                :bulk-actions="['delete' => 'Hapus', 'export' => 'Export']"
                search-placeholder="Cari log..." />
        </div>
    </div>
@endsection

@section('modal')
      {{-- Detail Modal --}}
    <x-bootstrap-modal id="auditdetailmodal" title="Detail Audit Log">
        <div id="audit-loading" class="py-4 text-center">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-2 mb-0 text-muted">Memuat data...</p>
        </div>

        <div id="audit-detail-content" style="display:none;">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold small text-uppercase text-muted">User</label>
                    <input type="text" id="d-user" class="form-control" readonly>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold small text-uppercase text-muted">Action</label>
                    <input type="text" id="d-action" class="form-control" readonly>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold small text-uppercase text-muted">Model Type</label>
                    <input type="text" id="d-model-type" class="form-control font-monospace" readonly>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold small text-uppercase text-muted">Model ID</label>
                    <input type="text" id="d-model-id" class="form-control" readonly>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold small text-uppercase text-muted">IP Address</label>
                    <input type="text" id="d-ip" class="form-control font-monospace" readonly>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold small text-uppercase text-muted">Waktu</label>
                    <input type="text" id="d-created-at" class="form-control" readonly>
                </div>
                <div class="col-md-12">
                    <label class="form-label fw-bold small text-uppercase text-muted">Description</label>
                    <input type="text" id="d-description" class="form-control" readonly>
                </div>
                <div class="col-md-12">
                    <label class="form-label fw-bold small text-uppercase text-muted">User Agent</label>
                    <textarea id="d-ua" class="form-control font-monospace" rows="2" readonly style="font-size:11px;"></textarea>
                </div>

                {{-- Old vs New Values --}}
                <div id="d-values-wrap" class="col-12" style="display:none;">
                    <div class="mt-1 row g-3">
                        <div class="col-md-6" id="d-old-wrap">
                            <label class="form-label fw-bold small text-uppercase text-muted"><i class="feather-arrow-left me-1 text-danger"></i> Old Values</label>
                            <textarea id="d-old" class="form-control font-monospace" rows="8" readonly
                                style="font-size:11px; background:#fff5f5; border-color:#fecdca;"></textarea>
                        </div>
                        <div class="col-md-6" id="d-new-wrap">
                            <label class="form-label fw-bold small text-uppercase text-muted"><i class="feather-arrow-right me-1 text-success"></i> New Values</label>
                            <textarea id="d-new" class="form-control font-monospace" rows="8" readonly
                                style="font-size:11px; background:#f0fdf4; border-color:#a7f3d0;"></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="px-0 pb-0 mt-3 modal-footer">
            <button class="btn btn-light-brand" data-bs-dismiss="modal">Tutup</button>
        </div>
    </x-bootstrap-modal>

@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Table initialized via x-data-table component

            $(document).on('click', '.btn-audit-detail', function(event) {
                event.stopPropagation();
                var id = $(this).data('id');
                var url = '{{ route('dashboard.audit_log.show', ':id') }}'.replace(':id', id);

                $('#audit-detail-content').hide();
                $('#audit-loading').show();
                $('#auditdetailmodal').modal('show');

                $.getJSON(url)
                    .done(function(log) {
                        fillAuditModal(log);
                        $('#audit-loading').hide();
                        $('#audit-detail-content').show();
                    })
                    .fail(function() {
                        $('#audit-loading').html('<p class="py-3 text-center text-danger"><i class="feather-alert-triangle me-1"></i> Gagal memuat data.</p>');
                    });
            });

            function fillAuditModal(log) {
                var actionMap = { created: 'Dibuat', updated: 'Diperbarui', deleted: 'Dihapus', login: 'Login', logout: 'Logout' };
                $('#auditdetailmodalLabel').text('Detail Audit Log #' + log.id);
                $('#d-user').val(log.user_name || 'System');
                $('#d-action').val(actionMap[(log.action || '').toLowerCase()] || (log.action || '—'));
                $('#d-model-type').val(log.model_type_short || '—');
                $('#d-model-id').val(log.model_id ? '#' + log.model_id : '—');
                $('#d-ip').val(log.ip_address || '—');
                $('#d-created-at').val(log.created_at_full || '—');
                $('#d-description').val(log.description || '—');
                $('#d-ua').val(log.user_agent || '—');

                var hasOld = log.old_values && Object.keys(log.old_values).length;
                var hasNew = log.new_values && Object.keys(log.new_values).length;

                if (hasOld || hasNew) {
                    $('#d-values-wrap').show();
                    $('#d-old-wrap').toggle(!!hasOld);
                    $('#d-new-wrap').toggle(!!hasNew);
                    $('#d-old').val(hasOld ? JSON.stringify(log.old_values, null, 2) : '');
                    $('#d-new').val(hasNew ? JSON.stringify(log.new_values, null, 2) : '');
                } else {
                    $('#d-values-wrap').hide();
                }
            }
        });
    </script>
@endpush
