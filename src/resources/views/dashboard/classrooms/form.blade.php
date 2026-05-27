<div class="card stretch stretch-full">
    <div class="card-header border-bottom">
        <h5 class="card-title">{{ $formTitle ?? ($classroom ? 'Edit Data Kelas: ' . $classroom->name : 'Form Tambah Kelas Baru') }}</h5>
    </div>
    <div class="card-body">
        <div class="mb-4 row align-items-center">
            <div class="col-lg-4">
                <label for="name" class="fw-semibold">Nama Kelas: <span class="text-danger">*</span></label>
            </div>
            <div class="col-lg-8">
                <div class="input-group">
                    <div class="input-group-text"><i class="feather-home"></i></div>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" name="name"
                        value="{{ old('name', $classroom?->name) }}" placeholder="Contoh: Kelas 10-A" required>
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>

        <div class="mb-4 row align-items-center">
            <div class="col-lg-4">
                <label for="classroom_type" class="fw-semibold">Tipe / Tingkat Kelas: <span class="text-danger">*</span></label>
            </div>
            <div class="col-lg-8">
                <div class="input-group">
                    <div class="input-group-text"><i class="feather-layers"></i></div>
                    <select data-select2-selector="default" class="form-control @error('classroom_type') is-invalid @enderror" name="classroom_type" required
                        data-select2-selector="default">
                        <option value="">Pilih Tipe</option>
                        <option value="Reguler" {{ old('classroom_type', $classroom?->classroom_type) == 'Reguler' ? 'selected' : '' }}>Reguler</option>
                        <option value="Unggulan" {{ old('classroom_type', $classroom?->classroom_type) == 'Unggulan' ? 'selected' : '' }}>Unggulan</option>
                    </select>
                    @error('classroom_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>

        <div class="mb-4 row align-items-center">
            <div class="col-lg-4">
                <label for="academic_year_id" class="fw-semibold">Tahun Akademik: <span class="text-danger">*</span></label>
            </div>
            <div class="col-lg-8">
                <div class="input-group">
                    <div class="input-group-text"><i class="feather-calendar"></i></div>
                    <select data-select2-selector="default" class="form-control @error('academic_year_id') is-invalid @enderror" id="academic_year_id"
                        name="academic_year_id" required data-select2-selector="default">
                        <option value="">Pilih Tahun Akademik</option>
                        @foreach ($academicYears as $year)
                            <option value="{{ $year->id }}" {{ old('academic_year_id', $classroom?->academic_year_id) == $year->id ? 'selected' : '' }}>
                                {{ $year->name }} {{ $year->is_active ? '(Aktif)' : '' }}
                            </option>
                        @endforeach
                    </select>
                    @if(!$classroom)
                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal"
                        data-bs-target="#addAcademicYearModal">
                        <i class="feather-plus"></i>
                    </button>
                    @endif
                    @error('academic_year_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>

        <div class="mb-4 row align-items-center">
            <div class="col-lg-12">
                <label for="teacher_ids" class="fw-semibold">Wali Kelas / Guru Pengajar:</label>
                <select name="teacher_ids[]" id="teacher_ids" class="form-control" data-select2-selector="default" multiple>
                    @foreach ($teachers as $teacher)
                        <option value="{{ $teacher->id }}" {{ in_array($teacher->id, old('teacher_ids', isset($classroom) ? $classroom->teachers->pluck('id')->toArray() : [])) ? 'selected' : '' }}>
                            {{ $teacher->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="mb-4 row align-items-center">
            <div class="col-lg-12">
                <label for="subject_ids" class="fw-semibold">Mata Pelajaran:</label>
                    <select name="subject_ids[]" id="subject_ids" class="form-control" data-select2-selector="default" multiple>
                        @foreach ($subjects as $subject)
                            <option value="{{ $subject->id }}" {{ in_array($subject->id, old('subject_ids', isset($classroom) ? $classroom->subjects->pluck('id')->toArray() : [])) ? 'selected' : '' }}>
                                {{ $subject->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>


        <div class="card-footer">
            <x-form-actions cancel-route="dashboard.classrooms.index" submit-label="{{ isset($classroom) ? 'Perbarui Kelas' : 'Simpan Kelas' }}" />
        </div>
    </div>
</div>
