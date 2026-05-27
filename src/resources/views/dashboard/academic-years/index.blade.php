@extends('layouts.app')

@section('title', 'Tahun Akademik')

@section('page-header')
    <x-page-header create-route="dashboard.academic-years.create">
        <x-slot:left>
            {{ Breadcrumbs::render('dashboard.academic_years.index') }}
        </x-slot:left>
        <x-slot:actions>
            <a href="{{ route('dashboard.academic-years.index') }}?export=true" class="btn btn-success">
                <i class="feather-download me-1"></i> Export
            </a>
        </x-slot:actions>
    </x-page-header>
@endsection

@section('content')
    {{-- Stats Cards --}}
    <div class="mb-4 row g-3">
        <div class="col-md-4">
            <x-card class="border-0 shadow-sm bg-primary text-white h-100">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <div class="avatar-text avatar-lg bg-white text-primary rounded flex-shrink-0">
                            <i class="feather-calendar fs-4"></i>
                        </div>
                    </div>
                    <div class="fw-bold text-white" style="font-size: 1.1rem; line-height: 1.4;">{{ $totalCount }}</div>
                    <div class="small text-white op-80 text-uppercase fw-bold">Total Tahun</div>
                </div>
            </x-card>
        </div>
        <div class="col-md-4">
            <x-card class="border-0 shadow-sm bg-success text-white h-100">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <div class="avatar-text avatar-lg bg-white text-success rounded flex-shrink-0">
                            <i class="feather-check-circle fs-4"></i>
                        </div>
                    </div>
                    <div class="fw-bold text-white" style="font-size: 1.1rem; line-height: 1.4;">{{ $activeCount }}</div>
                    <div class="small text-white op-80 text-uppercase fw-bold">Tahun Aktif</div>
                </div>
            </x-card>
        </div>
        <div class="col-md-4">
            <x-card class="border-0 shadow-sm bg-warning text-white h-100">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <div class="avatar-text avatar-lg bg-white text-warning rounded flex-shrink-0">
                            <i class="feather-alert-circle fs-4"></i>
                        </div>
                    </div>
                    <div class="fw-bold text-white" style="font-size: 1.1rem; line-height: 1.4;">{{ $inactiveCount }}</div>
                    <div class="small text-white op-80 text-uppercase fw-bold">Nonaktif</div>
                </div>
            </x-card>
        </div>
    </div>

    <div class="col-lg-12">
        <x-card full-height class="border-0 shadow-sm">
            @include('components.swal-flash')



            <x-data-table id="academicYearsTable" title="Daftar Tahun Akademik" :columns="[
                ['data' => 'name', 'title' => 'Tahun Akademik'],
                ['data' => 'start_date', 'title' => 'Mulai'],
                ['data' => 'end_date', 'title' => 'Selesai'],
                ['data' => 'is_active', 'title' => 'Status'],
            ]" action-route="academic-years" model="AcademicYear"
                :can-create="auth()->user()->can('create', App\Models\AcademicYear::class)"
                :can-edit="auth()->user()->can('update', App\Models\AcademicYear::class)"
                :can-delete="auth()->user()->can('delete', App\Models\AcademicYear::class)"
                :bulk-actions="['delete' => 'Hapus', 'export' => 'Export']"
                search-placeholder="Cari tahun akademik..." />
        </x-card>
    </div>

@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            const table = $('#academicYearsTable').DataTable();
            const baseUrl = '{{ route('dashboard.academic-years.index') }}';
        });
    </script>
@endpush
