@extends('layouts.app')

@section('title', 'Detail - ' . $academicYear->name ?? 'Academic Year')

@section('page-header')
    <x-page-header title="Detail Tahun Akademik">
        <x-slot:left>
            {{ Breadcrumbs::render('dashboard.academic-years.show', $academicYear) }}
        </x-slot:left>
    </x-page-header>

@endsection

@section('content')
    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header border-bottom">
                    <h5 class="card-title mb-0">Detail Tahun Akademik</h5>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <label class="fw-bold text-muted text-uppercase small">Tahun Akademik:</label>
                        <h5 class="mb-0">{{ $academicYear->name }}</h5>
                    </div>

                    <div class="row mb-4">
                        <div class="col-sm-6">
                            <label class="fw-bold text-muted text-uppercase small">Tanggal Mulai:</label>
                            <p class="mb-0">{{ \Carbon\Carbon::parse($academicYear->start_date)->format('d M Y') }}</p>
                        </div>
                        <div class="col-sm-6">
                            <label class="fw-bold text-muted text-uppercase small">Tanggal Selesai:</label>
                            <p class="mb-0">{{ \Carbon\Carbon::parse($academicYear->end_date)->format('d M Y') }}</p>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="fw-bold text-muted text-uppercase small">Status:</label>
                        <p class="mb-0">
                            @if ($academicYear->is_active)
                                <span class="badge bg-soft-success text-success">Aktif</span>
                            @else
                                <span class="badge bg-soft-secondary text-secondary">Tidak Aktif</span>
                            @endif
                        </p>
                    </div>

                    <div class="row">
                        <div class="col-sm-6">
                            <label class="fw-bold text-muted text-uppercase small">Total Hari:</label>
                            <h4 class="mb-0">
                                @php
                                    $start = \Carbon\Carbon::parse($academicYear->start_date);
                                    $end = \Carbon\Carbon::parse($academicYear->end_date);
                                    $days = $start->diffInDays($end);
                                @endphp
                                {{ $days }} hari
                            </h4>
                        </div>
                        <div class="col-sm-6">
                            <label class="fw-bold text-muted text-uppercase small">Dibuat:</label>
                            <p class="mb-0">{{ $academicYear->created_at->format('d M Y') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card">
                <div class="card-header border-bottom">
                    <h5 class="card-title mb-0">Aksi</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-column gap-2">
                        <a href="{{ route('dashboard.academic-years.index') }}" class="btn btn-outline-secondary">
                            <i class="feather-arrow-left me-2"></i>Kembali
                        </a>
                        @can('update', $academicYear)
                            <a href="{{ route('dashboard.academic-years.edit', $academicYear) }}"
                                class="btn btn-outline-primary">
                                <i class="feather-edit-3 me-2"></i>Edit
                            </a>
                        @endcan
                        @can('delete', $academicYear)
                            <form method="POST" action="{{ route('dashboard.academic-years.destroy', $academicYear) }}"
                                class="d-block">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger w-100 delete-confirm">
                                    <i class="feather-trash-2 me-2"></i>Hapus
                                </button>
                            </form>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                document.querySelectorAll('.delete-confirm').forEach(btn => {
                    btn.addEventListener('click', function(e) {
                        e.preventDefault();
                        if (confirm('Apakah Anda yakin ingin menghapus tahun akademik ini?')) {
                            this.closest('form').submit();
                        }
                    });
                });
            });
        </script>
    @endpush
@endsection
