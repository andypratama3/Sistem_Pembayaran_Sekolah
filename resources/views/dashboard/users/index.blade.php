@extends('layouts.app')

@section('title', 'Manajemen Pengguna')

@section('page-header')
    <x-page-header create-route="dashboard.settings.users.create">
        <x-slot:left>
            {{ Breadcrumbs::render('dashboard.settings.users.index') }}
        </x-slot:left>
    </x-page-header>
@endsection

@section('content')
    <div class="col-lg-12">
        <div class="card stretch stretch-full">
            @include('components.swal-flash')

            <div id="filterForm" data-table-target="#usersTable" class="p-4 m-0 border-bottom-0">
                <form class="row align-items-end g-3">
                    <div class="col-md-6">
                        <label class="mb-1 form-label fs-11 fw-bold text-uppercase text-muted">Filter Role</label>
                        <select name="role_id" class="border-0 form-select" data-select2-selector="default">
                            <option value="">Semua Role</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role->id }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="mb-1 form-label fs-11 fw-bold text-uppercase text-muted">Status Akun</label>
                        <select name="status" class="border-0 form-select" data-select2-selector="status">
                            <option value="">Semua Status</option>
                            <option value="active" data-bg="bg-success">Aktif</option>
                            <option value="inactive" data-bg="bg-danger">Nonaktif</option>
                        </select>
                    </div>
                </form>
            </div>

            <x-data-table id="usersTable" title="Daftar Pengguna Sistem" :columns="[
                ['data' => 'name', 'title' => 'Nama User'],
                ['data' => 'email', 'title' => 'Email'],
                ['data' => 'status', 'title' => 'Status'],
            ]" action-route="settings.users"
                model="User" :can-create="auth()->user()->can('create', App\Models\User::class)" :can-edit="auth()->user()->can('update', App\Models\User::class)" :can-delete="auth()->user()->can('delete', App\Models\User::class)" :bulk-actions="['delete' => 'Hapus', 'export' => 'Export']"
                search-placeholder="Cari user..." />
        </div>
    </div>



@endsection
@push('scripts')
    <script>
        $(document).ready(function() {
            const statusRouteTemplate = '{{ route('dashboard.settings.users.update-status', ['userRecord' => '__USER_ID__']) }}';

            $('#usersTable').on('change', '.change-status', function() {
                const $select = $(this);
                const userId = $select.data('id');
                const newStatus = $select.val();
                const previousStatus = $select.data('previous') ?? (newStatus === '1' ? '0' : '1');

                if (!userId) {
                    return;
                }

                $select.prop('disabled', true);

                $.ajax({
                    url: statusRouteTemplate.replace('__USER_ID__', userId),
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        status: newStatus
                    },
                    success: function(response) {
                        if (response.success) {
                            $select.data('previous', newStatus);
                            if (window.Toast) {
                                window.Toast.fire({
                                    icon: 'success',
                                    title: response.message ||
                                        'Status berhasil diperbarui'
                                });
                            }
                            return;
                        }

                        $select.val(previousStatus).trigger('change.select2');
                        Swal.fire('Gagal', response.message ||
                            'Gagal mengubah status pengguna.', 'error');
                    },
                    error: function(xhr) {
                        $select.val(previousStatus).trigger('change.select2');
                        const msg = xhr.responseJSON?.message ||
                            'Terjadi kesalahan saat mengubah status pengguna.';
                        Swal.fire('Gagal', msg, 'error');
                    },
                    complete: function() {
                        $select.prop('disabled', false);
                    }
                });
            });

            $('#usersTable').on('draw.dt', function() {
                $('#usersTable .change-status').each(function() {
                    $(this).data('previous', $(this).val());
                });
            });
        });
    </script>
@endpush
