<!-- resources/views/dashboard/students/form.blade.php -->
<div class="card border-top-0">
    <div class="p-0 card-header">
        <!-- Nav tabs (Duralux Premium Tabs) -->
        <ul class="flex-wrap text-center nav nav-tabs w-100 customers-nav-tabs" id="myTab" role="tablist">
            <li class="nav-item flex-fill border-top" role="presentation">
                <a href="javascript:void(0);" class="nav-link active" data-bs-toggle="tab" data-bs-target="#personalTab" role="tab">1. Data Pribadi</a>
            </li>
            <li class="nav-item flex-fill border-top" role="presentation">
                <a href="javascript:void(0);" class="nav-link" data-bs-toggle="tab" data-bs-target="#addressTab" role="tab">2. Alamat</a>
            </li>
            <li class="nav-item flex-fill border-top" role="presentation">
                <a href="javascript:void(0);" class="nav-link" data-bs-toggle="tab" data-bs-target="#guardianTab" role="tab">3. Orang Tua / Wali</a>
            </li>
            <li class="nav-item flex-fill border-top" role="presentation">
                <a href="javascript:void(0);" class="nav-link" data-bs-toggle="tab" data-bs-target="#academicTab" role="tab">4. Akademik & Keuangan</a>
            </li>
        </ul>
    </div>

    <div class="tab-content">
        <!-- Personal Tab -->
        <div class="tab-pane fade show active" id="personalTab" role="tabpanel">
            <div class="card-body">
                <x-form.input name="name" label="Nama Lengkap" icon="user" required model="{{ $student ?? null }}" placeholder="Masukkan nama lengkap" />

                <x-form.input name="nisn" label="NISN" icon="hash" required model="{{ $student ?? null }}" maxlength="10" placeholder="10 digit NISN"
                    help="Nomor Induk Siswa Nasional (10 digit)" />

                <x-form.select name="gender" label="Jenis Kelamin"
                    :options="['Laki-laki' => 'Laki-laki', 'Perempuan' => 'Perempuan']"
                    required model="{{ $student ?? null }}" />

                <div class="mb-4 row align-items-center">
                    <div class="col-lg-4">
                        <label class="fw-semibold text-dark">Tempat/Tgl Lahir: <span class="text-danger">*</span></label>
                    </div>
                    <div class="col-lg-8">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <x-form.input name="birth_place" type="text" placeholder="Tempat Lahir" required model="{{ $student ?? null }}" />
                            </div>
                            <div class="col-md-6">
                                <x-form.input name="birth_date" type="date" required model="{{ $student ?? null }}" />
                            </div>
                        </div>
                    </div>
                </div>

                <x-form.input name="phone" label="No. HP/Telp" icon="phone" required model="{{ $student ?? null }}" placeholder="08xxxxxxxxxx" />

                <x-form.file name="photo" label="Foto Profil" accept="image/*" preview model="{{ $student ?? null }}"
                    help="Format: JPG, JPEG, PNG, WEBP. Maksimal 2MB" />
            </div>
        </div>

        <!-- Address Tab -->
        <div class="tab-pane fade" id="addressTab" role="tabpanel">
            <div class="card-body">
                <x-form.textarea name="street" label="Alamat Lengkap" rows="3" model="{{ $student ?? null }}" placeholder="Jl. Contoh No. 123" />

                <div class="mb-4 row align-items-center">
                    <div class="col-lg-4">
                        <label class="fw-semibold">RT / RW:</label>
                    </div>
                    <div class="col-lg-8">
                        <div class="row g-3">
                            <div class="col-6">
                                <x-form.input name="rt" placeholder="001" model="{{ $student ?? null }}" />
                            </div>
                            <div class="col-6">
                                <x-form.input name="rw" placeholder="001" model="{{ $student ?? null }}" />
                            </div>
                        </div>
                    </div>
                </div>

                <x-form.input name="village_id" label="Kelurahan/Desa" placeholder="Kelurahan/Desa" model="{{ $student ?? null }}" />

                <x-form.select name="residence_type" label="Tipe Tempat Tinggal"
                    :options="['Rumah Pribadi' => 'Rumah Pribadi', 'Rumah Sewa' => 'Rumah Sewa', 'Kos' => 'Kos', 'Asrama' => 'Asrama', 'Lainnya' => 'Lainnya']"
                    model="{{ $student ?? null }}" />
            </div>
        </div>

        <!-- Guardian Tab -->
        <div class="tab-pane fade" id="guardianTab" role="tabpanel">
            <div class="card-body">
                <x-form.select name="guardian_type" label="Tipe Penjaga"
                    :options="['orang_tua' => 'Orang Tua', 'wali' => 'Wali']"
                    required model="{{ $student ?? null }}" />

                <hr class="mb-4">

                <div class="row">
                    <div class="col-md-6">
                        <x-form.input name="father_name" label="Nama Ayah" placeholder="Nama Ayah" model="{{ $student ?? null }}" />
                    </div>
                    <div class="col-md-6">
                        <x-form.input name="mother_name" label="Nama Ibu" placeholder="Nama Ibu" model="{{ $student ?? null }}" />
                    </div>
                </div>

                <x-form.textarea name="guardian_address" label="Alamat Wali" rows="3" model="{{ $student ?? null }}" placeholder="Alamat Lengkap Wali" />
            </div>
        </div>

        <!-- Academic Tab -->
        <div class="tab-pane fade" id="academicTab" role="tabpanel">
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-6">
                        <h6 class="fw-bold text-primary mb-3">Informasi Akademik</h6>
                        <div class="mb-4 row align-items-center">
                            <div class="col-lg-4">
                                <label for="classroom_id" class="fw-semibold text-dark">Kelas:</label>
                            </div>
                            <div class="col-lg-8">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="feather-home"></i></span>
                                    @php $currentClassroom = isset($student) ? $student->classrooms->first()?->id : null; @endphp
                                    <select class="form-control" id="classroom_id" name="classroom_id" data-select2-selector="default">
                                        <option value="">Pilih Kelas</option>
                                        @foreach ($classrooms as $classroom)
                                            <option value="{{ $classroom->id }}" {{ old('classroom_id', $currentClassroom) == $classroom->id ? 'selected' : '' }}>{{ $classroom->name }}</option>
                                        @endforeach
                                    </select>
                                    @if(!isset($student))
                                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addClassroomModal">
                                            <i class="feather-plus"></i>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <x-form.input name="entry_year" label="Tahun Masuk" type="number" placeholder="Contoh: 2023" model="{{ $student ?? null }}" />
                        <x-form.input name="entry_date" label="Tanggal Masuk" type="date" model="{{ $student ?? null }}" />
                        <x-form.input name="scholarship" label="Beasiswa" placeholder="Contoh: Prestasi / KIP" model="{{ $student ?? null }}" />
                    </div>

                    <div class="col-md-6 border-start">
                        <h6 class="fw-bold text-primary mb-3">Informasi Keuangan</h6>
                        <x-form.input name="va_number" label="Nomor VA" placeholder="Nomor Virtual Account" model="{{ $student ?? null }}" />
                        
                        <div class="mb-4 row align-items-center">
                            <div class="col-lg-4">
                                <label class="fw-semibold">Biaya SPP:</label>
                            </div>
                            <div class="col-lg-8">
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" name="spp" class="form-control" placeholder="0" value="{{ old('spp', $student->spp ?? '') }}">
                                </div>
                            </div>
                        </div>

                        <div class="mb-4 row align-items-center">
                            <div class="col-lg-4">
                                <label class="fw-semibold">Biaya DPP:</label>
                            </div>
                            <div class="col-lg-8">
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" name="dpp" class="form-control" placeholder="0" value="{{ old('dpp', $student->dpp ?? '') }}">
                                </div>
                            </div>
                        </div>

                        <div class="mb-4 row align-items-center">
                            <div class="col-lg-4">
                                <label class="fw-semibold">Biaya Seragam:</label>
                            </div>
                            <div class="col-lg-8">
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" name="uniform_fee" class="form-control" placeholder="0" value="{{ old('uniform_fee', $student->uniform_fee ?? '') }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @if(isset($student))
                <hr class="my-4">
                <x-form.select name="status" label="Status Siswa"
                    :options="['active' => 'Aktif', 'inactive' => 'Nonaktif', 'alumni' => 'Alumni']"
                    required model="{{ $student }}" />
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Sticky Footer (Manual Template Style) -->
<div class="mt-4 page-header">
    <div class="page-header-left d-flex align-items-center">
        <a href="{{ route('dashboard.students.index') }}" class="btn btn-light-brand">
            <i class="feather-arrow-left me-2"></i>
            <span>Kembali ke Daftar</span>
        </a>
    </div>
    <div class="page-header-right ms-auto">
        <button type="submit" class="btn btn-primary">
            <i class="feather-save me-2"></i>
            <span>{{ isset($student) ? 'Perbarui Data Siswa' : 'Simpan Data Siswa' }}</span>
        </button>
    </div>
</div>

@if(!isset($student))
<!-- Manual Bootstrap Modal -->
<div class="modal fade" id="addClassroomModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Kelas Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addClassroomForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Kelas <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" placeholder="Contoh: Kelas 10-A" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tipe Kelas <span class="text-danger">*</span></label>
                        <select data-select2-selector="default" class="form-control" name="classroom_type" required>
                            <option value="Reguler">Reguler</option>
                            <option value="Unggulan">Unggulan</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tahun Akademik <span class="text-danger">*</span></label>
                        <select data-select2-selector="default" class="form-control" name="academic_year_id" required>
                            @foreach($academicYears as $year)
                                <option value="{{ $year->id }}" {{ $year->is_active ? 'selected' : '' }}>{{ $year->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light-brand" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="saveClassroomBtn">Simpan Kelas</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        // Form Loading State
        $('form').on('submit', function() {
            const btn = $(this).find('button[type="submit"]');
            if (btn.length) {
                btn.html('<span class="spinner-border spinner-border-sm me-1"></span> Memproses...').prop('disabled', true);
            }
        });

        $('#addClassroomForm').on('submit', function(e) {
            e.preventDefault();
            const btn = $('#saveClassroomBtn');
            const originalText = btn.html();
            btn.html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...').prop('disabled', true);

            $.ajax({
                url: "{{ route('dashboard.classrooms.store') }}",
                method: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    if (response.status === 'success') {
                        const newOption = new Option(response.data.name, response.data.id, true, true);
                        $('#classroom_id').append(newOption).trigger('change');
                        $('#addClassroomModal').modal('hide');
                        $('#addClassroomForm')[0].reset();
                        Swal.fire({ icon: 'success', title: 'Berhasil', text: response.message, timer: 2000, showConfirmButton: false });
                    }
                },
                complete: function() { btn.html(originalText).prop('disabled', false); }
            });
        });
    });
</script>
@endpush
@endif
