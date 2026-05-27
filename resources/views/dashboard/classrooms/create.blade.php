@extends('layouts.app')

@section('title', 'Tambah Kelas')

@section('page-header')
    <x-page-header title="Tambah Kelas Baru">
        <x-slot:left>
            {{ Breadcrumbs::render('dashboard.classrooms.create') }}
        </x-slot:left>
        <x-slot:actions>
            <a href="{{ route('dashboard.classrooms.index') }}" class="btn btn-md btn-outline-secondary">
                <i class="feather-arrow-left me-2"></i>
                <span>Kembali ke Daftar</span>
            </a>
        </x-slot:actions>
    </x-page-header>
@endsection

@section('content')
    <div class="col-12">
        <form action="{{ route('dashboard.classrooms.store') }}" method="POST">
            @csrf

            @include('dashboard.classrooms.form', [
                'classroom' => null,
                'academicYears' => $academicYears,
                'submitLabel' => 'Buat Kelas',
                'formTitle' => 'Formulir Kelas Baru',
            ])
        </form>
    </div>
@endsection

@section('modal')
{{-- Modal Standardized --}}
    <x-bootstrap-modal id="addAcademicYearModal" title="Tambah Tahun Akademik">
        <form id="addAcademicYearForm">
            @csrf
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label small fw-bold text-uppercase text-muted">Nama Tahun Akademik <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="name" placeholder="Contoh: 2023/2024 Ganjil" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold text-uppercase text-muted">Tanggal Mulai <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="start_date" data-datepicker="true" placeholder="YYYY-MM-DD" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold text-uppercase text-muted">Tanggal Selesai <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="end_date" data-datepicker="true" placeholder="YYYY-MM-DD" required>
                </div>
                <div class="col-12">
                    <div class="form-check form-switch custom-switch">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" checked>
                        <label class="form-check-label" for="is_active">Aktifkan Tahun Ini</label>
                    </div>
                </div>
            </div>
            <div class="modal-footer px-0 pb-0 mt-3">
                <button type="button" class="btn btn-light-brand" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary" id="saveAcademicYearBtn">Simpan Tahun</button>
            </div>
        </form>
    </x-bootstrap-modal>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            $('#addAcademicYearForm').on('submit', function(e) {
                e.preventDefault();
                const btn = $('#saveAcademicYearBtn');
                const originalText = btn.html();

                btn.html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...').prop('disabled', true);

                $.ajax({
                    url: "{{ route('dashboard.academic-years.store') }}",
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        if (response.status === 'success') {
                            const year = response.data;
                            const displayName = year.name + (year.is_active ? ' (Aktif)' : '');
                            const newOption = new Option(displayName, year.id, true, true);
                            $('#academic_year_id').append(newOption).trigger('change');

                            $('#addAcademicYearModal').modal('hide');
                            $('#addAcademicYearForm')[0].reset();

                            Swal.fire({ icon: 'success', title: 'Berhasil', text: response.message, timer: 2000, showConfirmButton: false });
                        }
                    },
                    error: function(xhr) {
                        let message = 'Terjadi kesalahan saat menyimpan data.';
                        if (xhr.responseJSON && xhr.responseJSON.message) { message = xhr.responseJSON.message; }
                        Swal.fire({ icon: 'error', title: 'Kesalahan', text: message });
                    },
                    complete: function() {
                        btn.html(originalText).prop('disabled', false);
                    }
                });
            });
        });
    </script>
@endpush
