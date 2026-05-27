@extends('layouts.app')

@section('title', 'Detail Kelas')

@section('page-header')
    <x-page-header title="Manajemen Kelas">
        <x-slot:left>
            {{ Breadcrumbs::render('dashboard.classrooms.show', $classroom) }}
        </x-slot:left>
        <x-slot:actions>
            @can('update', $classroom)
                <a href="{{ route('dashboard.classrooms.edit', $classroom) }}" class="btn btn-md btn-warning">
                    <i class="feather-edit-3 me-2"></i>
                    <span>Edit Kelas</span>
                </a>
            @endcan
            <a href="{{ route('dashboard.classrooms.index') }}" class="btn btn-md btn-outline-secondary">
                <i class="feather-arrow-left me-2"></i>
                <span>Kembali ke Daftar</span>
            </a>
        </x-slot:actions>
    </x-page-header>
@endsection

@section('content')
    <div class="col-12">
        @include('components.swal-flash')

        {{-- Classroom Overview Card --}}
        <x-card class="border-0 shadow-sm mb-4">
            <div class="row align-items-center g-4">
                <div class="col-md-auto">
                    <div class="avatar-text avatar-xxl bg-soft-primary text-primary rounded border border-3 border-light shadow-sm">
                        <i class="feather-layers fs-1"></i>
                    </div>
                </div>
                <div class="col">
                    <h3 class="mb-1 fw-bold">{{ $classroom->name }}</h3>
                    <div class="d-flex align-items-center gap-3 text-muted">
                        <span><i class="feather-award me-1"></i>{{ Str::title($classroom->classroom_type) }}</span>
                        <span><i class="feather-calendar me-1"></i>{{ $classroom->academicYear?->name ?? 'TA -' }}</span>
                        <span><i class="feather-users me-1"></i>{{ $classroom->students_count ?? $classroom->students()->count() }} Siswa</span>
                    </div>
                    <div class="mt-2">
                        <span class="text-muted small fw-bold text-uppercase">Wali Kelas:</span>
                        @forelse($classroom->teachers as $teacher)
                            <span class="badge bg-soft-info text-info ms-1">{{ $teacher->name }}</span>
                        @empty
                            <span class="badge bg-soft-danger text-danger ms-1">Belum ada wali kelas</span>
                        @endforelse
                    </div>
                </div>
                <div class="col-md-auto ms-auto">
                    <div class="row g-2 text-center">
                        <div class="col-6 col-md-auto">
                            <div class="px-4 py-2 border rounded bg-body-secondary">
                                <div class="small text-muted text-uppercase">Rata-rata Nilai</div>
                                <div class="fw-bold fs-5 text-primary">{{ number_format($averageGrade, 1) }}</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-auto">
                            <div class="px-4 py-2 border rounded bg-body-secondary">
                                <div class="small text-muted text-uppercase">Kehadiran Hari Ini</div>
                                <div class="fw-bold fs-5 text-success">{{ round($attendanceToday) }}%</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </x-card>

        {{-- Tabs Navigation --}}
        <ul class="nav nav-pills custom-tabs mb-4 gap-2" id="classroomTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active py-2 px-4" id="students-tab" data-bs-toggle="tab" data-bs-target="#tab-students" type="button" role="tab">
                    <i class="feather-users me-2"></i>Daftar Siswa
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link py-2 px-4" id="schedule-tab" data-bs-toggle="tab" data-bs-target="#tab-schedule" type="button" role="tab">
                    <i class="feather-clock me-2"></i>Jadwal Pelajaran
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link py-2 px-4" id="performance-tab" data-bs-toggle="tab" data-bs-target="#tab-performance" type="button" role="tab">
                    <i class="feather-trending-up me-2"></i>Statistik & Laporan
                </button>
            </li>
        </ul>

        <div class="tab-content" id="classroomTabsContent">
            {{-- TAB SISWA --}}
            <div class="tab-pane fade show active" id="tab-students" role="tabpanel">
                <x-card class="border-0 shadow-sm">
                    <x-slot:header>
                        <div class="d-flex justify-content-between align-items-center w-100">
                            <h5 class="mb-0 card-title">Anggota Kelas</h5>
                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addStudentModal">
                                <i class="feather-user-plus me-1"></i>Tambah Siswa
                            </button>
                        </div>
                    </x-slot:header>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="bg-body-secondary">
                                <tr>
                                    <th>Nama Lengkap</th>
                                    <th>NISN</th>
                                    <th>Gender</th>
                                    <th>Telepon Orang Tua</th>
                                    <th class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="studentsTableBody">
                                @forelse($students as $student)
                                    <tr>
                                        <td>
                                            <div class="gap-3 hstack">
                                                <div class="avatar-text avatar-sm rounded-circle bg-soft-primary text-primary">
                                                    {{ strtoupper(substr($student->name, 0, 1)) }}
                                                </div>
                                                <div>
                                                    <div class="fw-semibold">{{ $student->name }}</div>
                                                    <div class="small text-muted">{{ $student->nis ?? '-' }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $student->nisn }}</td>
                                        <td>{{ $student->gender === 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                                        <td>{{ $student->parent_phone ?? '-' }}</td>
                                        <td class="text-end">
                                            <div class="gap-2 hstack justify-content-end">
                                                <a href="{{ route('dashboard.students.show', $student) }}" class="avatar-text avatar-sm" title="Profil Siswa">
                                                    <i class="feather-eye"></i>
                                                </a>
                                                <button class="avatar-text avatar-sm text-danger remove-student-btn"
                                                    data-student="{{ $student->id }}" data-classroom="{{ $classroom->id }}" title="Keluarkan dari Kelas">
                                                    <i class="feather-user-minus"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-5 text-center text-muted">
                                            <i class="feather-user-x fs-1 mb-3 d-block"></i>
                                            Belum ada siswa di kelas ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </x-card>
            </div>

            {{-- TAB JADWAL --}}
            <div class="tab-pane fade" id="tab-schedule" role="tabpanel">
                <x-card class="border-0 shadow-sm" title="Jadwal Mingguan">
                    <x-slot:header>
                        <div class="d-flex justify-content-between align-items-center w-100">
                            <h5 class="mb-0 card-title">Jadwal Pelajaran</h5>
                            <a href="{{ route('dashboard.schedules.index', ['classroom_id' => $classroom->id]) }}" class="btn btn-sm btn-primary">
                                <i class="feather-edit me-1"></i>Atur Jadwal
                            </a>
                        </div>
                    </x-slot:header>
                    <div class="text-center py-5">
                        <i class="feather-calendar fs-1 text-muted mb-3 d-block"></i>
                        <p class="text-muted">Gunakan tombol "Atur Jadwal" untuk mengelola jadwal pelajaran kelas ini.</p>
                        <a href="{{ route('dashboard.schedules.index', ['classroom_id' => $classroom->id]) }}" class="btn btn-outline-primary">Buka Pengaturan Jadwal</a>
                    </div>
                </x-card>
            </div>

            {{-- TAB STATISTIK --}}
            <div class="tab-pane fade" id="tab-performance" role="tabpanel">
                <div class="row g-4">
                    <div class="col-lg-6">
                        <x-card class="border-0 shadow-sm" title="Rata-rata Kehadiran (30 Hari Terakhir)">
                             <div class="text-center py-4">
                                 <h2 class="fw-bold text-success">95.8%</h2>
                                 <p class="text-muted">Kehadiran stabil di atas rata-rata sekolah (92%)</p>
                             </div>
                        </x-card>
                    </div>
                    <div class="col-lg-6">
                        <x-card class="border-0 shadow-sm" title="Sebaran Nilai Siswa">
                            <div class="text-center py-4">
                                 <h2 class="fw-bold text-primary">B+</h2>
                                 <p class="text-muted">Nilai rata-rata kelas untuk semua mata pelajaran</p>
                             </div>
                        </x-card>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('modal')
    <!-- Add Student Modal -->
    <div class="modal fade" id="addStudentModal" tabindex="-1" aria-labelledby="addStudentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="addStudentForm">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="addStudentModalLabel">Tambah Siswa ke Kelas</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="studentSearch" class="form-label">Cari Siswa (Ketik Nama atau NISN)</label>
                            <input type="text"
                                   id="studentSearch"
                                   class="form-control"
                                   placeholder="Ketik nama siswa atau NISN..."
                                   autocomplete="off">
                            <div class="mt-2 form-text">Ketik minimal 2 karakter untuk mencari siswa.</div>
                        </div>

                        <div id="studentSearchResults" class="mb-3 border rounded student-search-results" style="max-height: 250px; overflow-y: auto;">
                            <div class="py-4 text-center text-muted">
                                <em>Mulai mengetik untuk mencari siswa...</em>
                            </div>
                        </div>

                        <div class="mb-2 d-flex justify-content-between align-items-center">
                            <span class="text-muted small">Siswa Dipilih: <span id="selectedCount">0</span></span>
                            <button type="button" class="btn btn-sm btn-outline-danger" id="clearAllSelected" style="display: none;">
                                <i class="feather-x"></i> Hapus Semua
                            </button>
                        </div>

                        <div id="selectedStudentsList" class="border rounded" style="max-height: 200px; overflow-y: auto;">
                            <div class="py-4 text-center text-muted small" id="emptySelectedList">
                                <em>Belum ada siswa yang dipilih</em>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="saveStudentBtn" disabled>Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let searchTimeout = null;
    const classroomId = '{{ $classroom->id }}';
    let selectedStudents = [];

    function updateSelectedList() {
        const count = selectedStudents.length;
        $('#selectedCount').text(count);
        $('#saveStudentBtn').prop('disabled', count === 0);
        $('#clearAllSelected').toggle(count > 0);

        if (count === 0) {
            $('#selectedStudentsList').html('<div class="py-4 text-center text-muted small" id="emptySelectedList"><em>Belum ada siswa yang dipilih</em></div>');
            return;
        }

        let html = '';
        selectedStudents.forEach(function(student) {
            html += `
                <div class="p-2 border-bottom d-flex justify-content-between align-items-center" data-id="${student.id}">
                    <div class="gap-2 d-flex align-items-center">
                        <div class="avatar-text avatar-xs bg-soft-primary text-primary">${student.name.charAt(0).toUpperCase()}</div>
                        <div>
                            <span class="small fw-semibold">${student.name}</span>
                            <small class="text-muted d-block">NISN: ${student.nisn || '-'}</small>
                        </div>
                    </div>
                    <button type="button" class="p-0 btn btn-sm btn-link text-danger remove-selected-student" data-id="${student.id}">
                        <i class="feather-x"></i>
                    </button>
                </div>
            `;
        });
        $('#selectedStudentsList').html(html);
    }

    function addToSelected(student) {
        if (!selectedStudents.find(s => s.id === student.id)) {
            selectedStudents.push(student);
            updateSelectedList();
        }
    }

    function removeFromSelected(id) {
        selectedStudents = selectedStudents.filter(s => s.id !== id);
        updateSelectedList();
    }

    function isStudentSelected(id) {
        return selectedStudents.find(s => s.id === id);
    }

    // Student Search Autocomplete
    $('#studentSearch').on('input', function() {
        const query = $(this).val();
        if (searchTimeout) clearTimeout(searchTimeout);
        if (query.length < 2) {
            $('#studentSearchResults').html('<div class="py-4 text-center text-muted"><em>Ketik minimal 2 karakter...</em></div>');
            return;
        }
        searchTimeout = setTimeout(function() {
            searchStudents(query);
        }, 300);
    });

    function searchStudents(query) {
        $('#studentSearchResults').html('<div class="py-4 text-center"><div class="spinner-border spinner-border-sm"></div> Mencari...</div>');

        $.ajax({
            url: '{{ route("dashboard.students.search") }}',
            method: 'GET',
            data: { q: query, classroom_id: classroomId, limit: 20 },
            success: function(response) {
                const results = response.results || [];
                if (results.length === 0) {
                    $('#studentSearchResults').html('<div class="py-4 text-center text-muted"><em>Tidak ada siswa yang ditemukan.</em></div>');
                    return;
                }

                let html = '';
                results.forEach(function(student) {
                    const selected = isStudentSelected(student.id) ? 'checked' : '';
                    html += `
                        <div class="p-3 border-bottom">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input student-checkbox"
                                       id="student-${student.id}" data-id="${student.id}"
                                       data-name="${student.name}" data-nisn="${student.nisn}" ${selected}>
                                <label class="cursor-pointer form-check-label w-100" for="student-${student.id}">
                                    <div class="gap-2 d-flex align-items-center">
                                        <div class="avatar-text avatar-sm bg-soft-primary text-primary">${student.name.charAt(0).toUpperCase()}</div>
                                        <div>
                                            <h6 class="mb-0">${student.name}</h6>
                                            <small class="text-muted">NISN: ${student.nisn || '-'}</small>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>
                    `;
                });
                $('#studentSearchResults').html(html);
            },
            error: function() {
                $('#studentSearchResults').html('<div class="py-4 text-center text-danger"><em>Gagal mencari siswa.</em></div>');
            }
        });
    }

    // Toggle student selection
    $(document).on('change', '.student-checkbox', function() {
        const student = {
            id: $(this).data('id'),
            name: $(this).data('name'),
            nisn: $(this).data('nisn')
        };
        if ($(this).is(':checked')) {
            addToSelected(student);
        } else {
            removeFromSelected(student.id);
        }
    });

    // Remove from selected list
    $(document).on('click', '.remove-selected-student', function() {
        const id = $(this).data('id');
        removeFromSelected(id);
        $(`#student-${id}`).prop('checked', false);
    });

    // Clear all selected
    $('#clearAllSelected').on('click', function() {
        selectedStudents = [];
        $('.student-checkbox').prop('checked', false);
        updateSelectedList();
    });

    // Reset modal
    $('#addStudentModal').on('hidden.bs.modal', function() {
        $('#studentSearch').val('');
        selectedStudents = [];
        updateSelectedList();
        $('#studentSearchResults').html('<div class="py-4 text-center text-muted"><em>Mulai mengetik untuk mencari siswa...</em></div>');
    });

    // Add Students AJAX
    $('#addStudentForm').on('submit', function(e) {
        e.preventDefault();
        if (selectedStudents.length === 0) {
            Swal.fire({ icon: 'warning', title: 'Pilih Siswa', text: 'Silakan pilih siswa terlebih dahulu.' });
            return;
        }

        const btn = $('#saveStudentBtn');
        const originalText = btn.html();
        btn.html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...').prop('disabled', true);

        $.ajax({
            url: "{{ route('dashboard.classrooms.add-student', $classroom) }}",
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                student_ids: selectedStudents.map(s => s.id)
            },
            success: function(response) {
                if (response.status === 'success') {
                    $('#addStudentModal').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: response.message,
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                }
            },
            error: function(xhr) {
                let message = 'Terjadi kesalahan.';
                if (xhr.responseJSON && xhr.responseJSON.message) message = xhr.responseJSON.message;
                Swal.fire({ icon: 'error', title: 'Kesalahan', text: message });
                btn.html(originalText).prop('disabled', false);
            }
        });
    });
});
</script>
@endpush
