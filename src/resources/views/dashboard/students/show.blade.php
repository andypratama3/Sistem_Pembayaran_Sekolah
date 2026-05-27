@extends('layouts.app')

@section('title', 'Detail Siswa')

@section('page-header')
    <x-page-header title="Profil Siswa">
        <x-slot:left>
            {{ Breadcrumbs::render('dashboard.students.show', $student) }}
        </x-slot:left>
        <x-slot:actions>
            @can('update', $student)
                <a href="{{ route('dashboard.students.edit', $student) }}" class="btn btn-md btn-warning">
                    <i class="feather-edit-3 me-2"></i>
                    <span>Edit Profil</span>
                </a>
            @endcan
            <a href="{{ route('dashboard.students.index') }}" class="btn btn-md btn-outline-secondary">
                <i class="feather-arrow-left me-2"></i>
                <span>Kembali ke Daftar</span>
            </a>
        </x-slot:actions>
    </x-page-header>
@endsection

@section('content')
    @php
        $latestClassroom = $student->classrooms->last();
        $attendanceCount = $student->attendances->count();
        $presentCount = $student->attendances->where('status', 'present')->count();
        $attendanceRate = $attendanceCount > 0 ? round(($presentCount / $attendanceCount) * 100) : 0;
        $gradeAvg = $student->grades->avg('score');
        $paymentTotal = $student->payments->sum('gross_amount');
    @endphp

    <div class="col-12">
        @include('components.swal-flash')

        {{-- Profile Overview Card --}}
        <x-card class="border-0 shadow-sm mb-4">
            <div class="row align-items-center g-4">
                <div class="col-md-auto">
                    @if ($student->photo)
                        <img src="{{ Storage::url($student->photo) }}" alt="Foto Siswa"
                            class="rounded-circle border border-3 border-light shadow-sm" style="width: 120px; height: 120px; object-fit: cover;">
                    @else
                        <div class="avatar-text avatar-xxl bg-soft-primary text-primary rounded-circle border border-3 border-light shadow-sm">
                            {{ strtoupper(substr($student->name, 0, 1)) }}
                        </div>
                    @endif
                </div>
                <div class="col">
                    <h3 class="mb-1 fw-bold">{{ $student->name }}</h3>
                    <div class="d-flex align-items-center gap-3 text-muted">
                        <span><i class="feather-hash me-1"></i>{{ $student->nisn }}</span>
                        <span><i class="feather-map-pin me-1"></i>{{ $latestClassroom?->name ?? 'Belum ada kelas' }}</span>
                        @php
                            $statusConfig = [
                                'active' => ['class' => 'bg-soft-success text-success', 'label' => 'AKTIF'],
                                'inactive' => ['class' => 'bg-soft-danger text-danger', 'label' => 'NONAKTIF'],
                                'baru' => ['class' => 'bg-soft-info text-info', 'label' => 'BARU'],
                            ];
                            $status = strtolower($student->status ?? 'active');
                            $badge = $statusConfig[$status] ?? ['class' => 'bg-soft-secondary text-secondary', 'label' => strtoupper($status)];
                        @endphp
                        <span class="badge {{ $badge['class'] }}">{{ $badge['label'] }}</span>
                    </div>
                </div>
                <div class="col-md-auto ms-auto">
                    <div class="row g-2 text-center">
                        <div class="col-6">
                            <div class="px-4 py-2 border rounded bg-body-secondary">
                                <div class="small text-muted text-uppercase">Nilai Rata-rata</div>
                                <div class="fw-bold fs-5 text-primary">{{ $gradeAvg !== null ? number_format($gradeAvg, 2) : '-' }}</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="px-4 py-2 border rounded bg-body-secondary">
                                <div class="small text-muted text-uppercase">Kehadiran</div>
                                <div class="fw-bold fs-5 text-success">{{ $attendanceRate }}%</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </x-card>

        {{-- Tabs Navigation --}}
        <ul class="nav nav-pills custom-tabs mb-4 gap-2" id="studentTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active py-2 px-4" id="detail-tab" data-bs-toggle="tab" data-bs-target="#tab-detail" type="button" role="tab">
                    <i class="feather-user me-2"></i>Detail Profil
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link py-2 px-4" id="grades-tab" data-bs-toggle="tab" data-bs-target="#tab-grades" type="button" role="tab">
                    <i class="feather-book-open me-2"></i>Nilai <span class="badge bg-primary ms-2">{{ $student->grades->count() }}</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link py-2 px-4" id="attendance-tab" data-bs-toggle="tab" data-bs-target="#tab-attendance" type="button" role="tab">
                    <i class="feather-calendar me-2"></i>Presensi <span class="badge bg-warning ms-2 text-dark">{{ $student->attendances->count() }}</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link py-2 px-4" id="payments-tab" data-bs-toggle="tab" data-bs-target="#tab-payments" type="button" role="tab">
                    <i class="feather-credit-card me-2"></i>Pembayaran <span class="badge bg-success ms-2">{{ $student->payments->count() }}</span>
                </button>
            </li>
        </ul>

        <div class="tab-content" id="studentTabsContent">
            {{-- TAB DETAIL --}}
            <div class="tab-pane fade show active" id="tab-detail" role="tabpanel">
                <div class="row g-4">
                    <div class="col-lg-8">
                        <x-card class="border-0 shadow-sm h-100" title="Informasi Pribadi">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label text-muted small fw-bold text-uppercase mb-1">Email</label>
                                    <div class="fw-semibold">{{ $student->email ?? '-' }}</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted small fw-bold text-uppercase mb-1">No. HP</label>
                                    <div class="fw-semibold">{{ $student->phone ?? '-' }}</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted small fw-bold text-uppercase mb-1">Jenis Kelamin</label>
                                    <div class="fw-semibold">{{ $student->gender === 'L' ? 'Laki-laki' : 'Perempuan' }}</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted small fw-bold text-uppercase mb-1">Agama</label>
                                    <div class="fw-semibold">{{ ucfirst($student->religion ?? '-') }}</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted small fw-bold text-uppercase mb-1">Tempat/Tanggal Lahir</label>
                                    <div class="fw-semibold">{{ $student->birth_place ?? '-' }}, {{ $student->birth_date?->format('d M Y') ?? '-' }}</div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label text-muted small fw-bold text-uppercase mb-1">Alamat Lengkap</label>
                                    <div class="fw-semibold">
                                        {{ $student->street ?? '-' }}
                                        @if ($student->rt || $student->rw)
                                            (RT {{ $student->rt ?? '-' }}/RW {{ $student->rw ?? '-' }})
                                        @endif
                                        <br>
                                        {{ $student->village?->name ?? '' }}, {{ $student->district?->name ?? '' }}, {{ $student->regency?->name ?? '' }}, {{ $student->province?->name ?? '' }}
                                    </div>
                                </div>
                            </div>
                        </x-card>
                    </div>
                    <div class="col-lg-4">
                        <x-card class="border-0 shadow-sm h-100" title="Orang Tua / Wali">
                            <div class="mb-4">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <div class="avatar-text avatar-sm bg-soft-primary text-primary rounded"><i class="feather-user"></i></div>
                                    <div class="fw-bold">Informasi Ayah</div>
                                </div>
                                <div class="ps-5">
                                    <div class="fw-semibold">{{ $student->father_name ?? '-' }}</div>
                                    <div class="small text-muted">{{ $student->father_occupation ?? '-' }}</div>
                                </div>
                            </div>
                            <div class="mb-4">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <div class="avatar-text avatar-sm bg-soft-danger text-danger rounded"><i class="feather-user"></i></div>
                                    <div class="fw-bold">Informasi Ibu</div>
                                </div>
                                <div class="ps-5">
                                    <div class="fw-semibold">{{ $student->mother_name ?? '-' }}</div>
                                    <div class="small text-muted">{{ $student->mother_occupation ?? '-' }}</div>
                                </div>
                            </div>
                            <div>
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <div class="avatar-text avatar-sm bg-soft-info text-info rounded"><i class="feather-phone"></i></div>
                                    <div class="fw-bold">Kontak Darurat</div>
                                </div>
                                <div class="ps-5">
                                    <div class="fw-semibold">{{ $student->parent_phone ?? '-' }}</div>
                                    <div class="small text-muted">{{ $student->parent_email ?? '-' }}</div>
                                </div>
                            </div>
                        </x-card>
                    </div>
                    <div class="col-12">
                        <x-card class="border-0 shadow-sm" title="Riwayat Pendidikan & Beasiswa">
                            <div class="row g-4">
                                <div class="col-md-4">
                                    <label class="form-label text-muted small fw-bold text-uppercase mb-1">Sekolah Asal</label>
                                    <div class="fw-semibold">{{ $student->previous_school_name ?? '-' }}</div>
                                    <div class="small text-muted">{{ $student->previous_school_address ?? '-' }}</div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label text-muted small fw-bold text-uppercase mb-1">Masuk Sekolah</label>
                                    <div class="fw-semibold">Tahun {{ $student->entry_year ?? '-' }}</div>
                                    <div class="small text-muted">{{ $student->entry_date?->format('d M Y') ?? '-' }}</div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label text-muted small fw-bold text-uppercase mb-1">Beasiswa</label>
                                    <div class="fw-semibold">{{ $student->scholarship ?? 'Tidak Ada' }}</div>
                                </div>
                            </div>
                        </x-card>
                    </div>
                </div>
            </div>

            {{-- TAB NILAI --}}
            <div class="tab-pane fade" id="tab-grades" role="tabpanel">
                <x-card class="border-0 shadow-sm">
                    <x-slot:header>
                        <div class="d-flex justify-content-between align-items-center w-100">
                            <h5 class="mb-0 card-title">Riwayat Nilai Siswa</h5>
                            <a href="{{ route('dashboard.grades.create', ['student_id' => $student->id]) }}" class="btn btn-sm btn-primary">
                                <i class="feather-plus me-1"></i>Input Nilai
                            </a>
                        </div>
                    </x-slot:header>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="bg-body-secondary">
                                <tr>
                                    <th>Mata Pelajaran</th>
                                    <th>Kategori</th>
                                    <th class="text-center">Nilai</th>
                                    <th>Guru</th>
                                    <th>Tanggal</th>
                                    <th class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($student->grades as $grade)
                                    <tr>
                                        <td><div class="fw-bold">{{ $grade->subject?->name ?? '-' }}</div></td>
                                        <td>{{ ucfirst($grade->category ?? '-') }}</td>
                                        <td class="text-center">
                                            <span class="badge {{ $grade->score >= 75 ? 'bg-soft-success text-success' : 'bg-soft-danger text-danger' }} fs-14">
                                                {{ $grade->score }}
                                            </span>
                                        </td>
                                        <td>{{ $grade->teacher?->name ?? '-' }}</td>
                                        <td>{{ $grade->created_at->format('d/m/Y') }}</td>
                                        <td class="text-end">
                                            <a href="{{ route('dashboard.grades.edit', $grade) }}" class="btn btn-sm btn-light-brand"><i class="feather-edit-2"></i></a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">Belum ada data nilai tercatat.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </x-card>
            </div>

            {{-- TAB PRESENSI --}}
            <div class="tab-pane fade" id="tab-attendance" role="tabpanel">
                <x-card class="border-0 shadow-sm">
                    <x-slot:header>
                        <div class="d-flex justify-content-between align-items-center w-100">
                            <h5 class="mb-0 card-title">Riwayat Presensi</h5>
                            <a href="{{ route('dashboard.attendances.index', ['student_id' => $student->id]) }}" class="btn btn-sm btn-primary">
                                <i class="feather-external-link me-1"></i>Lihat Selengkapnya
                            </a>
                        </div>
                    </x-slot:header>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="bg-body-secondary">
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Sesi</th>
                                    <th class="text-center">Status</th>
                                    <th>Keterangan</th>
                                    <th class="text-end">Lokasi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($student->attendances->take(10) as $att)
                                    <tr>
                                        <td>{{ $att->date?->format('d/m/Y') ?? $att->created_at->format('d/m/Y') }}</td>
                                        <td>{{ $att->session ?? 'Default' }}</td>
                                        <td class="text-center">
                                            @php
                                                $attBadge = match($att->status) {
                                                    'present' => 'bg-success',
                                                    'sick' => 'bg-warning',
                                                    'permission' => 'bg-info',
                                                    'absent' => 'bg-danger',
                                                    default => 'bg-secondary'
                                                };
                                            @endphp
                                            <span class="badge {{ $attBadge }}">{{ strtoupper($att->status) }}</span>
                                        </td>
                                        <td>{{ $att->notes ?? '-' }}</td>
                                        <td class="text-end small text-muted">{{ $att->latitude ? $att->latitude . ', ' . $att->longitude : 'Manual' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-muted">Belum ada data presensi.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </x-card>
            </div>

            {{-- TAB PEMBAYARAN --}}
            <div class="tab-pane fade" id="tab-payments" role="tabpanel">
                <x-card class="border-0 shadow-sm">
                    <x-slot:header>
                        <div class="d-flex justify-content-between align-items-center w-100">
                            <h5 class="mb-0 card-title">Riwayat Pembayaran</h5>
                            <a href="{{ route('dashboard.payments.index', ['student_id' => $student->id]) }}" class="btn btn-sm btn-primary">
                                <i class="feather-plus me-1"></i>Tambah Pembayaran
                            </a>
                        </div>
                    </x-slot:header>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="bg-body-secondary">
                                <tr>
                                    <th>No. Invoice</th>
                                    <th>Item</th>
                                    <th>Metode</th>
                                    <th class="text-end">Jumlah</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-end">Tanggal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($student->payments as $pay)
                                    <tr>
                                        <td><code>{{ $pay->order_id }}</code></td>
                                        <td>{{ $pay->item_details ?? 'Biaya Sekolah' }}</td>
                                        <td>{{ strtoupper($pay->payment_type ?? '-') }}</td>
                                        <td class="text-end fw-bold text-dark">Rp {{ number_format($pay->gross_amount, 0, ',', '.') }}</td>
                                        <td class="text-center">
                                            <span class="badge {{ $pay->status === 'settlement' ? 'bg-soft-success text-success' : 'bg-soft-warning text-warning' }}">
                                                {{ strtoupper($pay->status) }}
                                            </span>
                                        </td>
                                        <td class="text-end text-muted">{{ $pay->created_at->format('d/m/Y') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">Belum ada riwayat pembayaran.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </x-card>
            </div>
        </div>
    </div>
@endsection
