<div class="card stretch stretch-full">
    <div class="card-header border-bottom">
        <h5 class="card-title">{{ $permission ? 'Edit Izin: ' . $permission->name : 'Tambah Izin Baru' }}</h5>
    </div>
    <div class="card-body">
        {{-- Name --}}
        <div class="row mb-4 align-items-center">
            <div class="col-lg-4">
                <label for="name" class="fw-semibold text-dark">Nama Izin: <span class="text-danger">*</span></label>
            </div>
            <div class="col-lg-8">
                <div class="input-group">
                    <span class="input-group-text"><i class="feather-shield"></i></span>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                        placeholder="Contoh: create-users" value="{{ old('name', $permission?->name) }}" required>
                </div>
                <small class="text-muted mt-2 d-block">Gunakan format lowercase dan dashboard (e.g.,
                    student-create)</small>
                @error('name')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
        </div>

        {{-- Guard Name --}}
        <div class="row mb-4 align-items-center">
            <div class="col-lg-4">
                <label for="guard_name" class="fw-semibold text-dark">Guard Name: <span
                        class="text-danger">*</span></label>
            </div>
            <div class="col-lg-8">
                <div class="input-group">
                    <span class="input-group-text"><i class="feather-lock"></i></span>
                    <input type="text" name="guard_name"
                        class="form-control @error('guard_name') is-invalid @enderror" placeholder="web"
                        value="{{ old('guard_name', $permission?->guard_name ?? 'web') }}" required>
                </div>
                @error('guard_name')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <x-form-actions cancel-route="dashboard.settings.permissions.index" submit-label="Simpan Izin"
            wrapper-class="page-header px-0 mt-4 rounded-0" />
    </div>
</div>
