@extends('layouts.app')

@section('title', 'Manajemen Kelas')

@section('page-header')
    <x-page-header create-route="dashboard.classrooms.create">
        <x-slot:left>
            {{ Breadcrumbs::render('dashboard.classrooms.index') }}
        </x-slot:left>
        <x-slot:actions>
            {{-- Tombol Export --}}
            <a href="{{ route('dashboard.classrooms.index') }}?export=true" class="btn btn-md btn-success">
                <i class="feather-download me-2"></i>
                <span>Export Excel</span>
            </a>
        </x-slot:actions>
    </x-page-header>
@endsection

@section('content')
    {{-- Stats / Quick Navigation --}}
    <div class="mb-4 row g-3">
        <div class="col-md-4">
            <x-card class="border-0 shadow-sm bg-primary text-white h-100">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <div class="avatar-text avatar-lg bg-white text-primary rounded flex-shrink-0">
                            <i class="feather-layers fs-4"></i>
                        </div>
                    </div>
                    <div class="fw-bold text-white" style="font-size: 1.1rem; line-height: 1.4;">{{ $totalCount ?? 0 }}</div>
                    <div class="small text-white op-80 text-uppercase fw-bold">Total Kelas</div>
                </div>
            </x-card>
        </div>
        <div class="col-md-4">
            <x-card class="border-0 shadow-sm bg-warning text-white h-100">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <div class="avatar-text avatar-lg bg-white text-warning rounded flex-shrink-0">
                            <i class="feather-user-minus fs-4"></i>
                        </div>
                    </div>
                    <div class="fw-bold text-white" style="font-size: 1.1rem; line-height: 1.4;">{{ $noTeacherCount ?? 0 }}</div>
                    <div class="small text-white op-80 text-uppercase fw-bold">Belum Ada Wali</div>
                </div>
            </x-card>
        </div>
        <div class="col-md-4">
            <x-card class="border-0 shadow-sm bg-success text-white h-100">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <div class="avatar-text avatar-lg bg-white text-success rounded flex-shrink-0">
                            <i class="feather-users fs-4"></i>
                        </div>
                    </div>
                    <div class="fw-bold text-white" style="font-size: 1.1rem; line-height: 1.4;">{{ $totalStudentCount ?? 0 }}</div>
                    <div class="small text-white op-80 op-80 text-uppercase fw-bold">Total Siswa Aktif</div>
                </div>
            </x-card>
        </div>
    </div>

    <div class="col-lg-12">
        <x-card full-height class="border-0 shadow-sm">
            @include('components.swal-flash')

            <div id="filterForm" data-table-target="#classroomsTable" class="p-4 border-bottom m-0">
                <div class="row align-items-center g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-uppercase">Tahun Akademik</label>
                        <select name="academic_year_id" class="form-control" data-select2-selector="default">
                            <option value="">Semua Tahun Akademik</option>
                            @foreach ($academicYears as $year)
                                <option value="{{ $year->id }}">{{ $year->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-uppercase">Tipe Kelas</label>
                        <select name="classroom_type" class="form-control" data-select2-selector="default">
                            <option value="">Semua Tipe</option>
                            <option value="reguler">Reguler</option>
                            <option value="ekstrakurikuler">Ekstrakurikuler</option>
                        </select>
                    </div>
                </div>
            </div>

            <x-data-table id="classroomsTable" title="Daftar Kelas" :columns="[
                ['data' => 'name', 'title' => 'Nama Kelas'],
                ['data' => 'academic_year', 'title' => 'Tahun Akademik'],
                ['data' => 'teacher_name', 'title' => 'Wali Kelas'],
            ]" action-route="classrooms" model="Classroom"
                :can-create="auth()->user()->can('create', App\Models\Classroom::class)"
                :can-edit="auth()->user()->can('update', App\Models\Classroom::class)"
                :can-delete="auth()->user()->can('delete', App\Models\Classroom::class)"
                :bulk-actions="['delete' => 'Hapus', 'export' => 'Export']"
                search-placeholder="Cari kelas..." />
        </x-card>
    </div>

@endsection
