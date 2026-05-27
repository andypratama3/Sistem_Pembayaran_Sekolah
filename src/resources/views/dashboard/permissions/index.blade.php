@extends('layouts.app')

@section('title', 'Manajemen Permissions')

@section('page-header')
    <x-page-header create-route="dashboard.settings.permissions.create">
        <x-slot:left>
            {{ Breadcrumbs::render('dashboard.settings.permissions.index') }}
        </x-slot:left>
    </x-page-header>
@endsection

@section('content')
    <div class="col-lg-12">
        <div class="card stretch stretch-full">
            @include('components.swal-flash')

            {{-- Redundant Filter Form Removed --}}

            <x-data-table id="permissionsTable" title="Daftar Izin Akses (Permissions)" :columns="[
                ['data' => 'name', 'title' => 'Nama Izin Akses'],
                ['data' => 'guard_name', 'title' => 'Guard'],
            ]" action-route="settings.permissions" model="Permission"
                :can-create="auth()->user()->can('create', Spatie\Permission\Models\Permission::class)" :can-edit="auth()->user()->can('update', Spatie\Permission\Models\Permission::class)" :can-delete="auth()->user()->can('delete', Spatie\Permission\Models\Permission::class)" :bulk-actions="['delete' => 'Hapus', 'export' => 'Export']"
                search-placeholder="Cari permission..." />
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Table initialized via x-data-table component
        });
    </script>
@endpush