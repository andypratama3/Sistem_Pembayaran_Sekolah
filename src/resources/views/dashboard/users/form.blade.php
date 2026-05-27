<div class="card-header border-bottom">
    <h5 class="card-title">{{ $user ? 'Edit Pengguna: ' . $user->name : 'Tambah Pengguna Baru' }}</h5>
</div>
<div class="card-body">
    {{-- Name --}}
    <div class="mb-4 row align-items-center">
        <div class="col-lg-4">
            <label for="name" class="fw-semibold">Nama Lengkap: <span class="text-danger">*</span></label>
        </div>
        <div class="col-lg-8">
            <div class="input-group">
                <div class="input-group-text"><i class="feather-user"></i></div>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                    placeholder="Nama Lengkap" value="{{ old('name', $user?->name) }}" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>

    {{-- Email --}}
    <div class="mb-4 row align-items-center">
        <div class="col-lg-4">
            <label for="email" class="fw-semibold">Alamat Email: <span class="text-danger">*</span></label>
        </div>
        <div class="col-lg-8">
            <div class="input-group">
                <div class="input-group-text"><i class="feather-mail"></i></div>
                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                    placeholder="email@example.com" value="{{ old('email', $user?->email) }}" required>
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>

    {{-- Password Section --}}
    <div class="mb-4 row align-items-center">
        <div class="col-lg-4">
            <label for="password" class="fw-semibold">Password: {!! $user
                ? ''
                : '<span
                                class="text-danger">*</span>' !!}</label>
        </div>
        <div class="col-lg-8">
            <div class="input-group">
                <div class="input-group-text"><i class="feather-lock"></i></div>
                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                    placeholder="Minimal 8 karakter" {{ $user ? '' : 'required' }} value="{{ old('password') }}">
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            @if ($user)
                <small class="text-muted">Biarkan kosong jika tidak ingin mengubah password</small>
            @endif
        </div>
    </div>

    <div class="mb-4 row align-items-center">
        <div class="col-lg-4">
            <label for="password_confirmation" class="fw-semibold">Konfirmasi Password:</label>
        </div>
        <div class="col-lg-8">
            <div class="input-group">
                <div class="input-group-text"><i class="feather-check-square"></i></div>
                <input type="password" name="password_confirmation" class="form-control" placeholder="Ulangi password">
            </div>
        </div>
    </div>

    {{-- Role / Permissions (if applicable) --}}
    @isset($roles)
        <div class="mb-4 row align-items-center">
            <div class="col-lg-4">
                <label class="fw-semibold">Pilih Peran: </label>
            </div>
            <div class="col-lg-8">
                <div class="input-group">
                    <div class="input-group-text"><i class="feather-shield"></i></div>
                    <select name="roles[]" class="form-control" data-select2-selector="default" multiple>
                        @foreach ($roles as $role)
                            <option value="{{ $role->name }}"
                                {{ isset($user) && $user->hasRole($role->name) ? 'selected' : '' }}>{{ $role->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    @endisset

    {{-- Status --}}
    <div class="mb-4 row align-items-center">
        <div class="col-lg-4">
            <label class="fw-semibold">Status Akun:</label>
        </div>
        <div class="col-lg-8">
            <div class="form-check form-switch">
                <input type="hidden" name="is_active" value="0">
                <input class="form-check-input" type="checkbox" name="is_active" id="isActive" value="1"
                    {{ old('is_active', $user?->is_active ?? true) ? 'checked' : '' }}>
                <label class="form-check-label" for="isActive">Aktifkan Akun</label>
            </div>
            <small class="text-muted">Akun nonaktif tidak akan bisa login.</small>
        </div>
    </div>
    <div class="gap-2 d-flex">
        <a href="{{ route('dashboard.settings.users.index') }}" class="btn btn-secondary">
            <i class="fas fa-times me-2"></i>Cancel
        </a>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save me-2"></i>Save User
        </button>
    </div>
</div>
