@extends('layouts.app')

@section('title', 'Manajemen Siswa')

@section('page-header')
    <x-page-header create-route="dashboard.students.create">
        <x-slot:left>
            {{ Breadcrumbs::render('dashboard.students.index') }}
        </x-slot:left>
        <x-slot:actions>
            {{-- Tombol Import --}}
            <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#importStudentModal">
                <i class="feather-upload me-2"></i>
                <span>Import DAPODIK</span>
            </button>

            {{-- Tombol Export --}}
            <a href="{{ route('dashboard.students.index') }}?export=true" class="btn btn-success">
                <i class="feather-download me-2"></i>
                <span>Export Excel</span>
            </a>

            {{-- Tombol Konfigurasi (Tahun Akademik) --}}
            <a href="{{ route('dashboard.academic-years.index') }}" class="btn btn-secondary">
                <i class="feather-settings me-2"></i>
                <span>Konfigurasi Tahun Akademik</span>
            </a>
        </x-slot:actions>
    </x-page-header>
@endsection

@section('content')
    {{-- Stats / Quick Navigation --}}
    <div class="mb-4 row g-3">
        <div class="col-md-3">
            <x-card class="text-white border-0 shadow-sm bg-primary h-100">
                <div class="py-3 card-body">
                    <div class="gap-3 mb-2 d-flex align-items-center">
                        <div class="flex-shrink-0 bg-white rounded avatar-text avatar-lg text-primary">
                            <i class="feather-users fs-4"></i>
                        </div>
                    </div>
                    <div class="text-white fw-bold" id="totalStudentsCount" style="font-size: 1.1rem; line-height: 1.4;">
                        {{ $totalCount ?? 0 }}</div>
                    <div class="text-white small op-80 text-uppercase fw-bold">Total Siswa</div>
                </div>
            </x-card>
        </div>
        <div class="col-md-3">
            <x-card class="text-white border-0 shadow-sm bg-success h-100">
                <div class="py-3 card-body">
                    <div class="gap-3 mb-2 d-flex align-items-center">
                        <div class="flex-shrink-0 bg-white rounded avatar-text avatar-lg text-success">
                            <i class="feather-user-check fs-4"></i>
                        </div>
                    </div>
                    <div class="text-white fw-bold" id="activeStudentsCount" style="font-size: 1.1rem; line-height: 1.4;">
                        {{ $activeCount ?? 0 }}</div>
                    <div class="text-white small op-80 text-uppercase fw-bold">Siswa Aktif</div>
                </div>
            </x-card>
        </div>
        <div class="col-md-3">
            <x-card class="text-white border-0 shadow-sm bg-warning h-100">
                <div class="py-3 card-body">
                    <div class="gap-3 mb-2 d-flex align-items-center">
                        <div class="flex-shrink-0 bg-white rounded avatar-text avatar-lg text-warning">
                            <i class="feather-alert-triangle fs-4"></i>
                        </div>
                    </div>
                    <div class="text-white fw-bold" id="noClassCount" style="font-size: 1.1rem; line-height: 1.4;">
                        {{ $noClassCount ?? 0 }}</div>
                    <div class="text-white small op-80 text-uppercase fw-bold">Tanpa Kelas</div>
                </div>
            </x-card>
        </div>
        <div class="col-md-3">
            <x-card class="text-white border-0 shadow-sm bg-info h-100">
                <div class="py-3 card-body">
                    <div class="gap-3 mb-2 d-flex align-items-center">
                        <div class="flex-shrink-0 bg-white rounded avatar-text avatar-lg text-info">
                            <i class="feather-user-plus fs-4"></i>
                        </div>
                    </div>
                    <div class="text-white fw-bold" id="newAdmissionCount" style="font-size: 1.1rem; line-height: 1.4;">
                        {{ $newCount ?? 0 }}</div>
                    <div class="text-white small op-80 text-uppercase fw-bold">Pendaftar Baru</div>
                </div>
            </x-card>
        </div>
    </div>

    <div class="col-lg-12">
        <x-card full-height class="border-0 shadow-sm">
            {{-- Filter Form --}}
            <div class="p-4 pb-0 border-bottom-0">
                <form id="filterForm" data-table-target="#studentsTable" class="row g-3 align-items-end">
                    @csrf
                    <div class="col-md-5">
                        <label class="mb-1 form-label fs-11 fw-bold text-uppercase text-muted">Filter Kelas</label>
                        <select name="classroom_id" class="border-0 form-select" data-select2-selector="default">
                            <option value="">Semua Kelas</option>
                            @foreach ($classrooms as $class)
                                <option value="{{ $class->id }}"
                                    {{ request('classroom_id') == $class->id ? 'selected' : '' }}>{{ $class->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label class="mb-1 form-label fs-11 fw-bold text-uppercase text-muted">Status Keaktifan</label>
                        <select name="status" class="border-0 form-select" data-select2-selector="status">
                            <option value="">Semua Status</option>
                            <option value="active" data-bg="bg-success"
                                {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                            <option value="inactive" data-bg="bg-secondary"
                                {{ request('status') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                            <option value="graduated" data-bg="bg-primary"
                                {{ request('status') === 'graduated' ? 'selected' : '' }}>Lulus</option>
                            <option value="dropped" data-bg="bg-danger"
                                {{ request('status') === 'dropped' ? 'selected' : '' }}>Putus Sekolah</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="button" id="resetFilter" class="btn btn-danger w-100 fw-bold">
                            <i class="feather-refresh-cw me-2"></i> Reset
                        </button>
                    </div>
                </form>
            </div>

            <x-data-table id="studentsTable" title="Daftar Siswa" :columns="[
                ['data' => 'name', 'title' => 'Nama Siswa'],
                ['data' => 'nisn', 'title' => 'NISN'],
                ['data' => 'classroom', 'title' => 'Kelas'],
                ['data' => 'status', 'title' => 'Status', 'className' => 'text-center'],
            ]" action-route="students" model="Student"
                :can-create="auth()->user()->can('create', App\Models\Student::class)" :can-edit="auth()->user()->can('update', App\Models\Student::class)" :can-delete="auth()->user()->can('delete', App\Models\Student::class)" :bulk-actions="['delete' => 'Hapus', 'export' => 'Export']"
                search-placeholder="Cari siswa (nama, NISN)..." />
        </x-card>
    </div>
@endsection
@section('modal')
    {{-- Import Modal --}}
    <div class="modal fade" id="importStudentModal" tabindex="-1" aria-labelledby="importStudentModalLabel"
        aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <form id="importStudentForm" action="{{ route('dashboard.students.import.store') }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="text-white modal-header bg-primary">
                        <h5 class="text-white modal-title" id="importStudentModalLabel">
                            <i class="text-white feather-upload me-2"></i>Impor Data Siswa (DAPODIK)
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div id="importInitialState">
                            <input type="hidden" id="importTotalRows" value="0">
                            <div class="py-4 text-center">
                                <div class="mb-3">
                                    <i class="feather-file-text text-muted" style="font-size: 4rem; opacity: 0.5;"></i>
                                </div>
                                <h6 class="mb-3 text-muted">Upload file Excel dari DAPODIK</h6>
                            </div>
                            <div class="mb-3">
                                <label for="file" class="form-label fw-bold">Pilih File</label>
                                <input type="file" class="form-control border-subtle" id="file" name="file"
                                    accept=".xlsx,.xls" required>
                                <div class="mt-2 form-text text-muted">
                                    <i class="feather-info me-1"></i>
                                    Format file harus sesuai dengan ekspor dari DAPODIK (.xlsx atau .xls)
                                </div>
                            </div>
                            <div class="mb-3">
                                <a href="{{ route('dashboard.students.import.template.download') }}"
                                    class="btn btn-sm btn-outline-primary">
                                    <i class="feather-download me-1"></i> Download Template DAPODIK
                                </a>
                                <div class="mt-1 form-text text-muted">
                                    <small>Unduh template untuk melihat format yang benar</small>
                                </div>
                            </div>
                            <div class="mb-0 alert alert-info d-flex align-items-center">
                                <i class="feather-alert-circle me-2"></i>
                                <small>File akan diproses dalam beberapa chunk. Progress akan ditampilkan secara
                                    real-time.</small>
                            </div>
                        </div>

                        <div id="importProgressState" style="display: none;">
                            <div class="py-3 text-center">
                                <div class="mb-3">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                </div>
                                <h5 id="importStatusText" class="fw-bold text-primary">Memproses data siswa...</h5>
                                <p class="mb-0 text-muted small" id="importSubStatus">Mohon tunggu, proses import sedang
                                    berjalan</p>
                            </div>

                            <div class="mb-4">
                                <div class="mb-2 d-flex justify-content-between">
                                    <span class="fw-bold">Progress</span>
                                    <span class="badge bg-primary fs-6" id="importPercentage">0%</span>
                                </div>
                                <div class="progress rounded-pill" style="height: 20px;">
                                    <div id="importProgressBar"
                                        class="progress-bar progress-bar-striped progress-bar-animated bg-primary"
                                        role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0"
                                        aria-valuemax="100">
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3 text-center row">
                                <div class="col-4">
                                    <div class="p-3 border rounded bg-body-secondary border-subtle">
                                        <h4 class="mb-1 fw-bold text-primary" id="importCountSuccess">0</h4>
                                        <small class="text-muted text-uppercase">Berhasil</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="p-3 border rounded bg-body-secondary border-subtle">
                                        <h4 class="mb-1 fw-bold text-danger" id="importCountFailed">0</h4>
                                        <small class="text-muted text-uppercase">Gagal</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="p-3 border rounded bg-body-secondary border-subtle">
                                        <h4 class="mb-1 fw-bold text-secondary" id="importCountTotal">0</h4>
                                        <small class="text-muted text-uppercase">Total</small>
                                    </div>
                                </div>
                            </div>

                            <div id="importErrorList" class="mt-3"
                                style="max-height: 150px; overflow-y: auto; display: none;">
                                <div class="alert alert-danger">
                                    <h6 class="alert-heading fw-bold">
                                        <i class="feather-alert-triangle me-1"></i>Kesalahan Import
                                    </h6>
                                    <ul id="importErrorItems" class="mb-0 small"></ul>
                                </div>
                            </div>
                        </div>

                        <div id="importSuccessState" style="display: none;">
                            <div class="py-4 text-center">
                                <div class="mb-3">
                                    <i class="feather-check-circle text-success" style="font-size: 5rem;"></i>
                                </div>
                                <h4 class="fw-bold text-success">Import Selesai!</h4>
                                <p class="text-muted" id="importSuccessMessage">Data siswa berhasil diimpor.</p>
                            </div>
                            <div class="row justify-content-center">
                                <div class="col-6">
                                    <div class="gap-2 d-grid">
                                        <a href="{{ route('dashboard.students.index') }}" class="btn btn-primary btn-lg">
                                            <i class="feather-eye me-1"></i>Lihat Data Siswa
                                        </a>
                                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                            Tutup
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer" id="importInitialFooter">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal"
                            id="importCancelBtn">Batal</button>
                        <button type="submit" class="btn btn-primary" id="importSubmitBtn">
                            <i class="feather-upload me-1"></i>Mulai Impor
                        </button>
                    </div>
                    <div class="modal-footer" id="importProgressFooter" style="display: none;">
                        <button type="button" class="btn btn-outline-danger" id="importStopBtn">
                            <i class="feather-x me-1"></i>Hentikan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @include('dashboard.students.scripts')
@endpush
