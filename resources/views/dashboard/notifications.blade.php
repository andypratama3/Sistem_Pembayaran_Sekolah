@extends('layouts.app')

@section('title', 'Notifications')

@section('page-header')
    <x-page-header title="Notifikasi">
        <x-slot:left>
            {{ Breadcrumbs::render('dashboard.notifications') }}
        </x-slot:left>
    </x-page-header>

@endsection

@section('content')
    <div class="col-lg-12 col-md-12 col-sm-12">
        <div class="card stretch stretch-full">

            @include('components.swal-flash')

            <form id="filterForm" class="m-3 row g-3">
                    @csrf
                <h5>Filter Notifikasi</h5>
                <div class="col-md-12">
                    <label class="form-label small fw-bold text-uppercase">Cari</label>
                    <input type="text" name="search" class="form-control" placeholder="Cari judul atau pesan...">
                </div>

                <div class="col-md-4">
                    <label class="form-label small fw-bold text-uppercase">Tipe</label>
                    <select name="type" class="form-control" data-select2-selector="type">
                        <option value="">Semua Tipe</option>
                        <option value="info">Informasi</option>
                        <option value="warning">Peringatan</option>
                        <option value="error">Error</option>
                        <option value="success">Sukses</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold text-uppercase">Status</label>
                    <select name="status" class="form-control" data-select2-selector="status">
                        <option value="">Semua Status</option>
                        <option value="read">Dibaca</option>
                        <option value="unread">Belum Dibaca</option>
                    </select>
                </div>
                <div class="col-md-4 align-items-end d-flex">
                    <button type="button" id="applyFilter" class="btn btn-primary">
                        <i class="feather-filter"></i> Filter
                    </button>
                    <button type="button" id="resetFilter" class="btn btn-outline-secondary ms-1">
                        <i class="feather-refresh-cw"></i> Reset
                    </button>
                </div>
            </form>

            <x-data-table id="notificationsTable" title="Daftar Notifikasi" :columns="[
                ['data' => 'type', 'name' => 'type', 'title' => 'Tipe'],
                ['data' => 'title', 'name' => 'title', 'title' => 'Judul'],
                ['data' => 'message', 'name' => 'message', 'title' => 'Pesan'],
                ['data' => 'created_at', 'name' => 'created_at', 'title' => 'Tanggal'],
                ['data' => 'status', 'name' => 'status', 'title' => 'Status'],
            ]" :api-route="route('dashboard.notifications.list')"
                :can-delete="auth()->user()->can('delete', App\Models\Notification::class)" :bulk-actions="['markRead' => 'Tandai Dibaca', 'delete' => 'Hapus']" search-placeholder="Cari notifikasi (judul, pesan)..." />

        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            const table = $('#notificationsTable').DataTable();
            const baseUrl = '{{ route('dashboard.notifications.list') }}';

            function buildFilterUrl() {
                const filters = {};
                $('#filterForm').serializeArray().forEach(function(item) {
                    if (item.value && item.value.trim() !== '') {
                        filters[item.name] = item.value.trim();
                    }
                });

                const queryString = $.param(filters);
                return queryString ? baseUrl + '?' + queryString : baseUrl;
            }

            $('#applyFilter').on('click', function() {
                try {
                    const url = buildFilterUrl();
                    table.ajax.url(url).load();
                } catch (error) {
                    console.error('Filter error:', error);
                    Swal.fire('Error', 'Failed to apply filter. Please try again.', 'error');
                }
            });

            $('#resetFilter').on('click', function() {
                try {
                    $('#filterForm')[0].reset();
                    $('[data-select2-selector]').val(null).trigger('change');
                    table.ajax.url(baseUrl).draw();
                } catch (error) {
                    console.error('Reset filter error:', error);
                }
            });

            $('#filterForm input[type="text"]').on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    $('#applyFilter').trigger('click');
                }
            });

            $('#notificationsTable').on('draw.dt', function() {
                $('#notificationsTable .checkbox').prop('checked', false);
            });
        });
    </script>
@endpush
