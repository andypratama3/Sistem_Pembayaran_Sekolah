@extends('layouts.app')

@section('title', 'Edit Tahun Akademik')

@section('page-header')
    <x-page-header title="Edit Tahun Akademik">
        <x-slot:left>
            {{ Breadcrumbs::render('dashboard.academic-years.edit', $academicYear) }}
        </x-slot:left>
    </x-page-header>

@endsection

@section('content')
    <div class="col-12 col-md-12">
        <div class="card">
            <div class="card-header border-bottom">
                <h5 class="card-title">Edit Tahun Akademik</h5>
            </div>
            <div class="card-body mt-3">
                <form action="{{ route('dashboard.academic-years.update', $academicYear) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label">Tahun Akademik (Contoh: 2023/2024 Ganjil) <span
                                class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" name="name"
                            value="{{ old('name', $academicYear->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tanggal Mulai <span class="text-danger">*</span></label>
                            <input type="text" data-datepicker="true" placeholder="YYYY-MM-DD" class="form-control @error('start_date') is-invalid @enderror"
                                name="start_date"
                                value="{{ old('start_date', $academicYear->start_date ? $academicYear->start_date->format('Y-m-d') : '') }}"
                                required>
                            @error('start_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tanggal Selesai <span class="text-danger">*</span></label>
                            <input type="text" data-datepicker="true" placeholder="YYYY-MM-DD" class="form-control @error('end_date') is-invalid @enderror"
                                name="end_date"
                                value="{{ old('end_date', $academicYear->end_date ? $academicYear->end_date->format('Y-m-d') : '') }}"
                                required>
                            @error('end_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="form-check form-switch">
                            <input type="hidden" name="is_active" value="0">
                            <input class="form-check-input" type="checkbox" name="is_active" id="isActive" value="1"
                                {{ old('is_active', $academicYear->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="isActive">Set sebagai Tahun Akademik Aktif</label>
                        </div>
                        <small class="text-muted">Mengaktifkan tahun akademik ini akan menonaktifkan tahun akademik aktif
                            lainnya.</small>
                    </div>

                    <x-form-actions cancel-route="dashboard.academic-years.index" />
                </form>
            </div>
        </div>
    </div>
@endsection
