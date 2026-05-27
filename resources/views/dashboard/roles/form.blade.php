<div class="card-header border-bottom">
    <h5 class="card-title">{{ $role ? 'Edit Peran: ' . $role->name : 'Tambah Peran Baru' }}</h5>
</div>
<div class="card-body">
    {{-- Name --}}
    <div class="mb-4 row align-items-center">
        <div class="col-lg-4">
            <label for="name" class="fw-semibold text-dark">Nama Peran: <span class="text-danger">*</span></label>
        </div>
        <div class="col-lg-8">
            <div class="input-group">
                <span class="input-group-text"><i class="feather-shield"></i></span>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                    placeholder="Contoh: Admin, Guru" value="{{ old('name', $role?->name) }}" required>
            </div>
            @error('name') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
        </div>
    </div>

    {{-- Guard Name --}}
    <div class="mb-4 row align-items-center">
        <div class="col-lg-4">
            <label for="guard_name" class="fw-semibold text-dark">Guard Name: <span class="text-danger">*</span></label>
        </div>
        <div class="col-lg-8">
            <div class="input-group">
                <span class="input-group-text"><i class="feather-lock"></i></span>
                <input type="text" name="guard_name" class="form-control @error('guard_name') is-invalid @enderror"
                    placeholder="web" value="{{ old('guard_name', $role?->guard_name ?? 'web') }}" required>
            </div>
            @error('guard_name') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
        </div>
    </div>

    {{-- Permissions --}}
    <div class="mb-4 row align-items-start">
        <div class="col-lg-4">
            <label for="permissions" class="fw-semibold text-dark">Hak Akses:</label>
        </div>
        <div class="col-lg-12">
                {{-- use checkbox --}}
            <div class="row g-3">
                @foreach ($permissions as $permission)
                    <div class="col-md-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $permission->id }}"
                                id="perm-{{ $permission->id }}"
                                {{ in_array($permission->id, old('permissions', $role?->permissions->pluck('id')->toArray() ?? [])) ? 'checked' : '' }}>
                            <label class="form-check-label" for="perm-{{ $permission->id }}">{{ $permission->name }}</label>
                        </div>
                    </div>
                @endforeach
            </div>
            @error('permissions') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
        </div>
    </div>

    <div class="mb-3 row d-flex">
        <div class="col-lg-4"></div>
        <div class="gap-2 col-lg-8 d-flex">
            <a href="{{ route('dashboard.settings.roles.index') }}" class="btn btn-outline-secondary">
                <i class="feather-x me-1"></i> Batal
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="feather-save me-1"></i> Simpan
            </button>
        </div>
    </div>
</div>
