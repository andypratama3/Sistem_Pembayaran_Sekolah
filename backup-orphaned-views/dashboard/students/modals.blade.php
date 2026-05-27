<x-bootstrap-modal id="addClassroomModal" title="Tambah Kelas Baru">
    <form id="addClassroomForm">
        @csrf
        <div class="row g-3">
            <div class="col-12">
                <label class="form-label small fw-bold text-uppercase text-muted">Nama Kelas <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="name" placeholder="Contoh: Kelas 10-A" required>
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-bold text-uppercase text-muted">Tipe Kelas <span class="text-danger">*</span></label>
                <select class="form-control" name="classroom_type" required>
                    <option value="Reguler">Reguler</option>
                    <option value="Unggulan">Unggulan</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-bold text-uppercase text-muted">Tahun Akademik <span class="text-danger">*</span></label>
                <select class="form-control" name="academic_year_id" required>
                    @foreach($academicYears as $year)
                        <option value="{{ $year->id }}" {{ $year->is_active ? 'selected' : '' }}>{{ $year->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="modal-footer px-0 pb-0 mt-3">
            <button type="button" class="btn btn-light-brand" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-primary" id="saveClassroomBtn">Simpan Kelas</button>
        </div>
    </form>
</x-bootstrap-modal>
