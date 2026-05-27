@extends('layouts.app')

@section('title', 'Tambah Tahun Akademik')

@section('page-header')
    <x-page-header title="Tambah Tahun Akademik">
        <x-slot:left>
            {{ Breadcrumbs::render('dashboard.academic_years.create') }}
        </x-slot:left>
    </x-page-header>
@endsection

@section('content')
    <div class="col-lg-12">
        <div class="card stretch stretch-full">
            <div class="card-header border-bottom">
                <h5 class="card-title text-capitalize">Form Tahun Akademik Baru</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('dashboard.academic-years.store') }}" method="POST">
                    @csrf

                    <div class="row mb-4 align-items-center">
                        <div class="col-lg-4">
                            <label for="name" class="fw-semibold">Tahun Akademik: <span class="text-danger">*</span></label>
                        </div>
                        <div class="col-lg-8">
                            <div class="input-group">
                                <div class="input-group-text"><i class="feather-calendar"></i></div>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name"
                                       value="{{ old('name') }}" placeholder="Contoh: 2023/2024 Ganjil" required>
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4 align-items-center">
                        <div class="col-lg-4">
                            <label for="start_date" class="fw-semibold">Tanggal Mulai: <span class="text-danger">*</span></label>
                        </div>
                        <div class="col-lg-8">
                            <div class="input-group">
                                <div class="input-group-text"><i class="feather-calendar"></i></div>
                                <input type="text" data-datepicker="true" placeholder="YYYY-MM-DD" class="form-control @error('start_date') is-invalid @enderror" id="start_date" name="start_date"
                                       value="{{ old('start_date') }}" required>
                                @error('start_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4 align-items-center">
                        <div class="col-lg-4">
                            <label for="end_date" class="fw-semibold">Tanggal Selesai: <span class="text-danger">*</span></label>
                        </div>
                        <div class="col-lg-8">
                            <div class="input-group">
                                <div class="input-group-text"><i class="feather-calendar"></i></div>
                                <input type="text" data-datepicker="true" placeholder="YYYY-MM-DD" class="form-control @error('end_date') is-invalid @enderror" id="end_date" name="end_date"
                                       value="{{ old('end_date') }}" required>
                                @error('end_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4 align-items-center">
                        <div class="col-lg-4">
                            <label class="fw-semibold">Pengaturan Aktif:</label>
                        </div>
                        <div class="col-lg-8">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" id="isActive" value="1" {{ old('is_active') ? 'checked' : '' }}>
                                <label class="form-check-label" for="isActive">Set sebagai Tahun Aktif</label>
                            </div>
                            <small class="text-muted">Aksi ini akan menonaktifkan tahun akademik aktif lainnya.</small>
                        </div>
                    </div>

                    <x-form-actions cancel-route="dashboard.academic-years.index" />
                </form>
            </div>
        </div>
    </div>
@endsection
