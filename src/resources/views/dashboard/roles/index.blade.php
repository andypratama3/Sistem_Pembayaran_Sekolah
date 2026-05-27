@extends('layouts.app')

@section('title', 'Manajemen Role')

@section('page-header')
    <x-page-header create-route="dashboard.settings.roles.create">
        <x-slot:left>
            {{ Breadcrumbs::render('dashboard.settings.roles.index') }}
        </x-slot:left>
    </x-page-header>
@endsection

@section('content')
    <div class="col-lg-12">
        <div class="card stretch stretch-full">
            @include('components.swal-flash')


            <x-data-table id="rolesTable" title="Daftar Role User" :columns="[
                ['data' => 'name', 'title' => 'Nama Role'],
                ['data' => 'guard_name', 'title' => 'Guard'],
            ]" action-route="settings.roles" model="Role"
                :can-create="auth()->user()->can('create', \Models\Role::class)" :can-edit="auth()->user()->can('update', \Models\Role::class)" :can-delete="auth()->user()->can('delete', Spatie\Permission\Models\Role::class)" :bulk-actions="['delete' => 'Hapus', 'export' => 'Export']"
                search-placeholder="Cari role..." />
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Additional custom logic if needed
        });
    </script>
@endpush
