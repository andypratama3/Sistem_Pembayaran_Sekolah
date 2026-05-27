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

<x-bootstrap-modal id="addTeacherModal" title="Tambah Guru Baru">
    <form id="addTeacherForm">
        @csrf
        <div class="mb-3">
            <label class="form-label small fw-bold text-uppercase text-muted">Nama Guru <span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="name" placeholder="Nama lengkap guru" required>
        </div>
        <div class="mb-3">
            <label class="form-label small fw-bold text-uppercase text-muted">Status <span class="text-danger">*</span></label>
            <select class="form-control" name="status" required>
                <option value="1">Aktif</option>
                <option value="0">Tidak Aktif</option>
            </select>
        </div>
        <div class="modal-footer px-0 pb-0 mt-3">
            <button type="button" class="btn btn-light-brand" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-primary" id="saveTeacherBtn">Simpan Guru</button>
        </div>
    </form>
</x-bootstrap-modal>

<x-bootstrap-modal id="addSubjectModal" title="Tambah Mata Pelajaran Baru">
    <form id="addSubjectForm">
        @csrf
        <div class="mb-3">
            <label class="form-label small fw-bold text-uppercase text-muted">Nama Mata Pelajaran <span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="name" placeholder="Contoh: Matematika" required>
        </div>
        <div class="modal-footer px-0 pb-0 mt-3">
            <button type="button" class="btn btn-light-brand" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-primary" id="saveSubjectBtn">Simpan Mapel</button>
        </div>
    </form>
</x-bootstrap-modal>
