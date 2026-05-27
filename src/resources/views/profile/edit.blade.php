@extends('layouts.app')

@section('title', 'Profil Pengguna')

@section('page-header')
    <x-page-header title="Profil Saya" />
@endsection

@section('content')
    @php
        $employee = $user->employee;
        $teacher = $employee?->teacher;
        $student = $user->student;
        $profileImage = $user->avatar ? asset('storage/' . $user->avatar) : asset('assets/images/avatar/1.png');
    @endphp

    <div class="main-content">
        <div class="row">
            {{-- Kolom Kiri: Ringkasan Profil --}}
            <div class="col-xxl-4 col-xl-5">
                <div class="card stretch stretch-full">
                    <div class="card-body">
                        <div class="mb-4 text-center">
                            <div class="mx-auto mb-3 wd-150 ht-150 position-relative">
                                <div
                                    class="overflow-hidden border border-gray-300 avatar-image wd-150 ht-150 border-5 rounded-circle">
                                    <img src="{{ $profileImage }}" alt="{{ $user->name }}" class="img-fluid">
                                </div>
                            </div>
                            <div class="mb-4">
                                <h4 class="mb-1 fs-18 fw-bold">{{ $user->name }}</h4>
                                <p class="mb-0 text-muted">{{ $user->email }}</p>
                                <span class="mt-2 badge bg-soft-primary text-primary">
                                    {{ ucfirst(str_replace('_', ' ', $user->getRoleNames()->first() ?? 'User')) }}
                                </span>
                            </div>
                        </div>

                        <div class="pt-3 list-group list-group-flush border-top">
                            <div class="px-0 border-0 list-group-item d-flex justify-content-between align-items-center">
                                <span class="text-muted"><i class="feather-calendar me-2"></i>Terdaftar Sejak</span>
                                <span class="fw-medium text-dark">{{ $user->created_at->format('d M, Y') }}</span>
                            </div>
                            <div class="px-0 border-0 list-group-item d-flex justify-content-between align-items-center">
                                <span class="text-muted"><i class="feather-shield me-2"></i>Status Akun</span>
                                @if ($user->is_active)
                                    <span class="badge bg-soft-success text-success">Aktif</span>
                                @else
                                    <span class="badge bg-soft-danger text-danger">Tidak Aktif</span>
                                @endif
                            </div>
                            <div class="px-0 border-0 list-group-item d-flex justify-content-between align-items-center">
                                <span class="text-muted"><i class="feather-user-check me-2"></i>Tipe Profil</span>
                                @if ($student)
                                    <span class="badge bg-soft-info text-info">Siswa</span>
                                @elseif ($teacher)
                                    <span class="badge bg-soft-warning text-warning">Guru</span>
                                @elseif ($employee)
                                    <span class="badge bg-soft-secondary text-secondary">Karyawan</span>
                                @elseif(Auth::user()->roles()->first()->name)
                                    <span class="text-black badge bg-danger">{{ ucfirst(Auth::user()->roles()->first()->name) }}</span>
                                @else
                                    <span class="badge bg-soft-primary text-primary">Pengguna</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Kolom Kanan: Detail & Pengaturan --}}
            <div class="col-xxl-8 col-xl-7">
                <div class="card stretch stretch-full">
                    <div class="p-0 card-header">
                        <ul class="nav nav-tabs nav-tabs-custom w-100" id="profileTab" role="tablist">
                            <li class="nav-item flex-fill" role="presentation">
                                <a class="py-3 text-center nav-link active" id="info-tab" data-bs-toggle="tab"
                                    href="#info" role="tab">
                                    <i class="feather-user me-2"></i>Informasi Pribadi
                                </a>
                            </li>
                            <li class="nav-item flex-fill" role="presentation">
                                <a class="py-3 text-center nav-link" id="password-tab" data-bs-toggle="tab" href="#password"
                                    role="tab">
                                    <i class="feather-lock me-2"></i>Keamanan
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content" id="profileTabContent">

                            {{-- Tab 1: Informasi Pribadi --}}
                            <div class="tab-pane fade show active" id="info" role="tabpanel">
                                <form method="post" action="{{ route('dashboard.profile.update') }}" class="row g-3">
                                    @csrf
                                    @method('patch')

                                    <div class="col-12">
                                        <h5 class="mb-4 fw-bold">Update Informasi Profil</h5>
                                    </div>

                                    <div class="mb-3 col-md-6 col-sm-12">
                                        <label class="form-label fw-semibold text-dark">Nama Lengkap</label>
                                        <input type="text" name="name" class="form-control"
                                            value="{{ old('name', $user->name) }}" required>
                                        @error('name')
                                            <div class="mt-1 text-danger fs-12">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3 col-md-6 col-sm-12">
                                        <label class="form-label fw-semibold text-dark">Alamat Email</label>
                                        <input type="email" name="email" class="form-control"
                                            value="{{ old('email', $user->email) }}" required>
                                        @error('email')
                                            <div class="mt-1 text-danger fs-12">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mt-4 col-12">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="feather-save me-2"></i>Simpan Perubahan
                                        </button>
                                    </div>
                                </form>

                                <hr class="my-5">

                                <div class="row g-3">
                                    <div class="col-12">
                                        <h5 class="mb-3 fw-bold">Detail User Terkait</h5>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="p-3 border rounded-3 h-100">
                                            <h6 class="mb-3 fw-semibold">Informasi Akun</h6>
                                            <div class="mb-2 d-flex justify-content-between">
                                                <span class="text-muted">Role</span>
                                                <span
                                                    class="text-dark fw-medium">{{ $user->getRoleNames()->join(', ') ?: '-' }}</span>
                                            </div>
                                            <div class="mb-2 d-flex justify-content-between">
                                                <span class="text-muted">Email Verifikasi</span>
                                                <span
                                                    class="text-dark fw-medium">{{ $user->email_verified_at ? $user->email_verified_at->format('d M Y, H:i') : 'Belum terverifikasi' }}</span>
                                            </div>
                                            <div class="d-flex justify-content-between">
                                                <span class="text-muted">User ID</span>
                                                <span class="text-dark fw-medium">{{ $user->id }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    @if ($employee)
                                        <div class="col-md-6">
                                            <div class="p-3 border rounded-3 h-100">
                                                <h6 class="mb-3 fw-semibold">Data Karyawan</h6>
                                                <div class="mb-2 d-flex justify-content-between">
                                                    <span class="text-muted">Nama</span>
                                                    <span class="text-dark fw-medium">{{ $employee->name ?: '-' }}</span>
                                                </div>
                                                <div class="mb-2 d-flex justify-content-between">
                                                    <span class="text-muted">NIP / NIK</span>
                                                    <span
                                                        class="text-dark fw-medium">{{ ($employee->nip ?: '-') . ' / ' . ($employee->nik ?: '-') }}</span>
                                                </div>
                                                <div class="mb-2 d-flex justify-content-between">
                                                    <span class="text-muted">Telepon</span>
                                                    <span class="text-dark fw-medium">{{ $employee->phone ?: '-' }}</span>
                                                </div>
                                                <div class="d-flex justify-content-between">
                                                    <span class="text-muted">Posisi</span>
                                                    <span
                                                        class="text-dark fw-medium">{{ $employee->staffPosition?->name ?: '-' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    @if ($teacher)
                                        <div class="col-12">
                                            <div class="p-3 border rounded-3 h-100">
                                                <h6 class="mb-3 fw-semibold">Data Guru</h6>
                                                <div class="row g-3">
                                                    <div class="col-md-4">
                                                        <div class="d-flex justify-content-between">
                                                            <span class="text-muted">Nama Guru</span>
                                                            <span
                                                                class="text-dark fw-medium">{{ $teacher->name ?: '-' }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="d-flex justify-content-between">
                                                            <span class="text-muted">Pendidikan</span>
                                                            <span
                                                                class="text-dark fw-medium">{{ $teacher->graduation ?: '-' }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="d-flex justify-content-between">
                                                            <span class="text-muted">Status</span>
                                                            <span
                                                                class="text-dark fw-medium">{{ $teacher->status ?: '-' }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-12">
                                                        <span class="mb-2 text-muted d-block">Mata Pelajaran</span>
                                                        @forelse ($teacher->subjects as $subject)
                                                            <span
                                                                class="mb-1 badge bg-soft-primary text-primary me-1">{{ $subject->name }}</span>
                                                        @empty
                                                            <span class="text-muted">Belum ada mata pelajaran.</span>
                                                        @endforelse
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    @if ($student)
                                        <div class="col-12">
                                            <div class="p-3 border rounded-3 h-100">
                                                <h6 class="mb-3 fw-semibold">Data Siswa</h6>
                                                <div class="row g-3">
                                                    <div class="col-md-3">
                                                        <div class="d-flex justify-content-between">
                                                            <span class="text-muted">Nama</span>
                                                            <span
                                                                class="text-dark fw-medium">{{ $student->name ?: '-' }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="d-flex justify-content-between">
                                                            <span class="text-muted">NISN</span>
                                                            <span
                                                                class="text-dark fw-medium">{{ $student->nisn ?: '-' }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="d-flex justify-content-between">
                                                            <span class="text-muted">Telepon</span>
                                                            <span
                                                                class="text-dark fw-medium">{{ $student->phone ?: '-' }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="d-flex justify-content-between">
                                                            <span class="text-muted">Tahun Masuk</span>
                                                            <span
                                                                class="text-dark fw-medium">{{ $student->entry_year ?: '-' }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="d-flex justify-content-between">
                                                            <span class="text-muted">Kontak Orang Tua</span>
                                                            <span
                                                                class="text-dark fw-medium">{{ $student->parent_phone ?: '-' }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="d-flex justify-content-between">
                                                            <span class="text-muted">Email Orang Tua</span>
                                                            <span
                                                                class="text-dark fw-medium">{{ $student->parent_email ?: '-' }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-12">
                                                        <span class="mb-2 text-muted d-block">Kelas Terdaftar</span>
                                                        @forelse ($student->classrooms as $classroom)
                                                            <span class="mb-1 badge bg-soft-info text-info me-1">
                                                                {{ $classroom->name }}
                                                                @if ($classroom->classroom_type)
                                                                    ({{ $classroom->classroom_type }})
                                                                @endif
                                                            </span>
                                                        @empty
                                                            <span class="text-muted">Belum terdaftar di kelas mana
                                                                pun.</span>
                                                        @endforelse
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    @if (!$employee && !$student)
                                        <div class="col-12">
                                            <div class="mb-0 border alert alert-light">
                                                Data relasi untuk akun ini belum tersedia.
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Tab 2: Keamanan --}}
                            <div class="tab-pane fade" id="password" role="tabpanel">
                                <form method="post" action="{{ route('password.update') }}" class="row g-3">
                                    @csrf
                                    @method('put')

                                    <div class="col-12">
                                        <h5 class="mb-4 fw-bold">Ganti Kata Sandi</h5>
                                        <p class="mb-4 text-muted fs-13">Pastikan akun Anda menggunakan kata sandi yang
                                            panjang dan acak untuk tetap aman.</p>
                                    </div>

                                    <div class="mb-3 col-12">
                                        <label class="form-label fw-semibold text-dark">Kata Sandi Saat Ini</label>
                                        <input type="password" name="current_password" class="form-control" required>
                                        @error('current_password', 'updatePassword')
                                            <div class="mt-1 text-danger fs-12">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3 col-md-6">
                                        <label class="form-label fw-semibold text-dark">Kata Sandi Baru</label>
                                        <input type="password" name="password" class="form-control" required>
                                        @error('password', 'updatePassword')
                                            <div class="mt-1 text-danger fs-12">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3 col-md-6">
                                        <label class="form-label fw-semibold text-dark">Konfirmasi Kata Sandi</label>
                                        <input type="password" name="password_confirmation" class="form-control"
                                            required>
                                    </div>

                                    <div class="mt-4 col-12">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="feather-key me-2"></i>Update Kata Sandi
                                        </button>
                                    </div>
                                </form>

                                <hr class="my-5">

                                <div class="col-12">
                                    <h5 class="mb-3 fw-bold text-danger">Hapus Akun</h5>
                                    <p class="mb-4 text-muted fs-13">Setelah akun Anda dihapus, semua sumber daya dan
                                        datanya akan dihapus secara permanen. Sebelum menghapus akun Anda, harap unduh data
                                        atau informasi apa pun yang ingin Anda simpan.</p>

                                    <button type="button" class="btn btn-danger" data-bs-toggle="modal"
                                        data-bs-target="#confirm-user-deletion">
                                        <i class="feather-trash-2 me-2"></i>Hapus Akun
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('modal')
{{-- Modal Hapus Akun --}}
<div class="modal fade" id="confirm-user-deletion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="post" action="{{ route('dashboard.profile.destroy') }}" class="modal-content">
            @csrf
            @method('delete')
            <div class="modal-header">
                <h5 class="modal-title">Apakah Anda yakin ingin menghapus akun?</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted">Setelah akun Anda dihapus, semua sumber daya dan datanya akan dihapus secara
                    permanen. Harap masukkan kata sandi Anda untuk mengonfirmasi bahwa Anda ingin menghapus akun Anda
                    secara permanen.</p>
                <div class="mt-3">
                    <label class="form-label visually-hidden">Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Masukkan Password"
                        required>
                    @error('password', 'userDeletion')
                        <div class="mt-1 text-danger fs-12">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-danger">Hapus Akun Secara Permanen</button>
            </div>
        </form>
    </div>
</div>
@endsection
