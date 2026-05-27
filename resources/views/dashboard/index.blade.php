@extends('layouts.app')

@section('title', 'Ringkasan Dashboard')

@section('page-header')
    <x-page-header title="Ringkasan Dashboard">
        <x-slot:left>
            {{ Breadcrumbs::render('dashboard.index') }}
        </x-slot:left>
        <x-slot:actions>
            <div class="gap-2 d-flex align-items-center">
                <span class="px-3 py-2 border badge rounded-pill bg-soft-success text-success d-none d-md-inline-flex align-items-center border-success border-opacity-10">
                    <span class="bg-success rounded-circle me-2" style="width: 6px; height: 6px; display: inline-block;"></span>
                    Sistem Online
                </span>
                <button type="button" class="btn btn-sm btn-light-brand rounded-circle" onclick="location.reload()" style="width: 32px; height: 32px; padding: 0;">
                    <i class="feather-refresh-cw fs-12"></i>
                </button>
            </div>
        </x-slot:actions>
    </x-page-header>
@endsection

@section('content')
    {{-- Dashboard Filter Bar --}}
    <div class="col-12">
        <div class="mb-3">
            <x-card class="border-0 shadow-sm premium-card">
                <form id="dashboardFilter" method="GET" class="row g-3 align-items-end">
                    @csrf
                    <div class="col-lg-3 col-md-6">
                        <label class="mb-1 form-label fs-10 fw-bold text-uppercase text-muted">Tahun Akademik</label>
                        <select name="academic_year" class="form-control" data-select2-selector="default" data-placeholder="Pilih Tahun Akademik">
                            <option value="">Semua</option>
                            @foreach ($stats['academic_years'] as $year)
                                <option value="{{ $year->id }}" {{ request('academic_year') == $year->id ? 'selected' : '' }}>{{ $year->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <label class="mb-1 form-label fs-10 fw-bold text-uppercase text-muted">Tanggal Mulai</label>
                        <input type="text" name="date_from" data-datepicker="true" class="form-control form-control-sm" value="{{ request('date_from') }}" placeholder="YYYY-MM-DD">
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <label class="mb-1 form-label fs-10 fw-bold text-uppercase text-muted">Tanggal Akhir</label>
                        <input type="text" name="date_to" data-datepicker="true" class="form-control form-control-sm" value="{{ request('date_to') }}" placeholder="YYYY-MM-DD">
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="gap-2 d-flex">
                            <button type="submit" class="btn btn-sm btn-premium">
                                <i class="feather-filter me-1 fs-12"></i> Terapkan
                            </button>
                            @if(request()->hasAny(['date_from', 'date_to', 'academic_year']))
                                <a href="{{ route('dashboard.index') }}" class="btn btn-sm btn-soft-danger">
                                    <i class="feather-rotate-ccw fs-12"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                </form>
            </x-card>
        </div>

        {{-- KPI Cards --}}
        <div class="mb-4 row g-3">
        <x-analytics-card
            title="Siswa Aktif"
            :value="$stats['total_students']"
            icon="users"
            iconBg="bg-soft-primary"
            iconColor="text-primary"
            id="stat-total-students"
            route="{{ route('dashboard.students.index') }}"
            routeLabel="<i class='feather-users fs-10 me-1'></i> Daftar Siswa"
        />
        <x-analytics-card
            title="Guru & Staf"
            :value="$stats['total_teachers'] + $stats['total_employees']"
            icon="user-check"
            iconBg="bg-soft-warning"
            iconColor="text-warning"
            id="stat-total-staff"
            badge="Verifikasi"
            badgeClass="badge bg-soft-primary text-primary"
            route="{{ route('dashboard.teachers.index') }}"
            routeLabel="<i class='feather-user-check fs-10 me-1'></i> Daftar Guru"
        />
        <x-analytics-card
            title="Kehadiran Hari Ini"
            :value="$stats['attendance_today']"
            icon="activity"
            iconBg="bg-soft-info"
            iconColor="text-info"
            format="percent"
            id="stat-attendance-today"
            badge="Langsung"
            badgeClass="badge bg-soft-info text-info"
            route="{{ route('dashboard.attendances.index') }}"
            routeLabel="<i class='feather-calendar fs-10 me-1'></i> Rekap Absensi"
        />
        <x-analytics-card
            title="Tagihan Tertunda"
            :value="$stats['outstanding_payments']"
            icon="dollar-sign"
            iconBg="bg-soft-danger"
            iconColor="text-danger"
            format="currency"
            :divisor="1000000"
            :decimal="1"
            suffix="jt"
            id="stat-outstanding-payments"
            badge="Pending"
            badgeClass="badge bg-soft-danger text-danger"
            route="{{ route('dashboard.payments.index') }}"
            routeLabel="<i class='feather-credit-card fs-10 me-1'></i> Kelola Tagihan"
        />
    </div>

    {{-- Row 2: Quick Actions & Live Stats --}}
    <div class="row">
        <div class="col-xxl-8 col-lg-7">
            <x-card full-height class="border-0 shadow-sm">
                <x-slot:header>
                    <div class="d-flex align-items-center justify-content-between w-100">
                        <div>
                            <h5 class="mb-0 card-title">Aksi Cepat</h5>
                            <span class="text-muted small">Akses cepat ke fitur utama</span>
                        </div>
                        <i class="feather-zap text-primary"></i>
                    </div>
                </x-slot:header>
                <div class="row g-3">
                    <div class="col-sm-4 col-6">
                        <a href="{{ route('dashboard.admissions.create') }}" class="py-3 btn btn-quick-action w-100 flex-column">
                            <i class="mb-2 feather-user-plus fs-20"></i>
                            <span class="fw-bold small text-uppercase">Siswa Baru</span>
                        </a>
                    </div>
                    <div class="col-sm-4 col-6">
                        <a href="{{ route('dashboard.payments.create') }}" class="py-3 btn btn-quick-action w-100 flex-column">
                            <i class="mb-2 feather-credit-card fs-20"></i>
                            <span class="fw-bold small text-uppercase">Input Bayar</span>
                        </a>
                    </div>
                    <div class="col-sm-4 col-6">
                        <a href="{{ route('dashboard.attendances.index') }}" class="py-3 btn btn-quick-action w-100 flex-column">
                            <i class="mb-2 feather-check-square fs-20"></i>
                            <span class="fw-bold small text-uppercase">Absensi</span>
                        </a>
                    </div>
                    <div class="col-sm-4 col-6">
                        <a href="{{ route('dashboard.report-cards.index') }}" class="py-3 btn btn-quick-action w-100 flex-column">
                            <i class="mb-2 feather-book-open fs-20"></i>
                            <span class="fw-bold small text-uppercase">Kelola Rapor</span>
                        </a>
                    </div>
                    <div class="col-sm-4 col-6">
                        <a href="{{ route('dashboard.payroll.index') }}" class="py-3 btn btn-quick-action w-100 flex-column">
                            <i class="mb-2 feather-briefcase fs-20"></i>
                            <span class="fw-bold small text-uppercase">Gaji & Payroll</span>
                        </a>
                    </div>
                    <div class="col-sm-4 col-6">
                        <a href="{{ route('dashboard.whatsapp-chat.index') }}" class="py-3 btn btn-quick-action w-100 flex-column">
                            <i class="mb-2 feather-message-circle fs-20"></i>
                            <span class="fw-bold small text-uppercase">Kirim Pesan</span>
                        </a>
                    </div>
                </div>
            </x-card>
        </div>
        <div class="col-xxl-4 col-lg-5">
            <x-card full-height class="overflow-hidden text-white border-0 shadow-sm bg-premium-gradient">
                <div class="position-relative" style="z-index: 1;">
                    <div class="top-0 p-3 opacity-25 position-absolute end-0" style="z-index: -1;">
                        <i class="feather-award fs-80"></i>
                    </div>
                    <h5 class="mb-4 text-white">Selamat Datang, {{ $user->name }}!</h5>
                    <p class="text-white text-opacity-75 small">Anda memiliki
                        <strong id="stat-my-active-tasks">{{ $stats['tugas_aktif_saya'] }}</strong> tugas yang menunggu hari ini. Selesaikan sisa
                        rapor dan verifikasi pembayaran untuk menutup hari.</p>

                    <div class="gap-3 pt-4 mt-4 border-white hstack border-top border-opacity-10">
                        <div>
                            <span class="text-white text-opacity-50 d-block small">Tahun Ajaran</span>
                            <span class="text-white fw-bold">{{ $stats['academic_years']->where('is_active', true)->first()->name ?? 'N/A' }}</span>
                        </div>
                        <div class="ms-auto text-end">
                            <a href="{{ route('dashboard.tasks.index') }}" class="btn btn-sm btn-white text-primary fw-bold btn-premium-light">
                                Buka Tugas <i class="feather-arrow-right ms-1 small"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </x-card>
        </div>
    </div>

    {{-- Row 3: Advanced Analytics --}}
    <div class="row">
        <div class="col-xxl-4 col-lg-6">
            <x-card full-height class="border-0 shadow-sm premium-card">
                <x-slot:header>
                    <div class="d-flex align-items-center justify-content-between w-100">
                        <h5 class="mb-0 card-title">Demografi & Status</h5>
                        <i class="feather-pie-chart text-primary"></i>
                    </div>
                </x-slot:header>
                <div class="row align-items-center">
                    <div class="col-6">
                        <div id="genderPieChart" style="min-height: 180px;"></div>
                    </div>
                    <div class="col-6">
                        <div class="gap-2 d-flex flex-column">
                            @foreach($stats['siswa_by_status'] as $status => $count)
                                <div class="p-2 rounded bg-body-secondary border-start border-3 border-{{ $status == 'active' ? 'success' : ($status == 'inactive' ? 'secondary' : 'primary') }}">
                                    <div class="smaller text-muted text-uppercase fw-bold">{{ $status }}</div>
                                    <div class="fw-bold">{{ number_format($count) }} <small class="text-muted">Siswa</small></div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </x-card>
        </div>
        <div class="col-xxl-4 col-lg-6">
            <x-card full-height class="border-0 shadow-sm premium-card">
                <x-slot:header>
                    <div class="d-flex align-items-center justify-content-between w-100">
                        <h5 class="mb-0 card-title">Ulang Tahun Hari Ini</h5>
                        <span class="px-2 border badge rounded-pill bg-soft-danger text-danger border-danger border-opacity-10">Special Day</span>
                    </div>
                </x-slot:header>
                <div class="p-0 card-body">
                    <div class="list-group list-group-flush">
                        @forelse ($stats['student_birthdays'] as $person)
                            <div class="gap-3 py-3 border-0 list-group-item d-flex align-items-center">
                                <div class="border border-opacity-25 rounded-circle avatar-text avatar-md bg-soft-warning text-warning border-warning">
                                    <i class="feather-gift"></i>
                                </div>
                                <div>
                                    <span class="fw-bold d-block">{{ $person->name }}</span>
                                    <span class="text-muted small">Merayakan ulang tahun hari ini! 🎂</span>
                                </div>
                                <div class="ms-auto">
                                    <button class="btn btn-sm btn-icon btn-soft-primary rounded-circle"><i class="feather-send"></i></button>
                                </div>
                            </div>
                        @empty
                            <div class="py-5 text-center text-muted">
                                <i class="mb-2 opacity-25 feather-calendar fs-24"></i>
                                <p class="mb-0 small">Tidak ada yang berulang tahun hari ini.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </x-card>
        </div>
        <div class="col-xxl-4 col-lg-12">
            <x-card full-height class="border-0 shadow-sm premium-card">
                <x-slot:header>
                    <div class="d-flex align-items-center justify-content-between w-100">
                        <h5 class="mb-0 card-title">Monitoring SDM (Cuti)</h5>
                        <a href="{{ route('dashboard.leave-requests.index') }}" class="small fw-bold">Kelola</a>
                    </div>
                </x-slot:header>
                <div class="row g-3">
                    @php
                        $pendingCuti = $stats['cuti_stats']['pending'] ?? 0;
                        $approvedCuti = $stats['cuti_stats']['approved'] ?? 0;
                        $rejectedCuti = $stats['cuti_stats']['rejected'] ?? 0;
                        $totalCuti = $pendingCuti + $approvedCuti + $rejectedCuti;
                    @endphp
                    <div class="col-12">
                        <div class="mb-2 d-flex justify-content-between smaller fw-bold text-uppercase">
                            <span>Persetujuan Cuti</span>
                            <span>{{ $totalCuti > 0 ? round(($approvedCuti / $totalCuti) * 100) : 0 }}% Terproses</span>
                        </div>
                        <div class="mb-4 progress rounded-pill" style="height: 10px;">
                            <div class="progress-bar bg-success" style="width: {{ $totalCuti > 0 ? ($approvedCuti / $totalCuti) * 100 : 0 }}%"></div>
                            <div class="progress-bar bg-warning" style="width: {{ $totalCuti > 0 ? ($pendingCuti / $totalCuti) * 100 : 0 }}%"></div>
                            <div class="progress-bar bg-danger" style="width: {{ $totalCuti > 0 ? ($rejectedCuti / $totalCuti) * 100 : 0 }}%"></div>
                        </div>
                        <div class="text-center row g-2">
                            <div class="col-4">
                                <div class="p-2 rounded bg-soft-warning">
                                    <h5 class="mb-0 fw-bold text-warning">{{ $pendingCuti }}</h5>
                                    <small class="smaller text-muted text-uppercase">Pending</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-2 rounded bg-soft-success">
                                    <h5 class="mb-0 fw-bold text-success">{{ $approvedCuti }}</h5>
                                    <small class="smaller text-muted text-uppercase">Disetujui</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-2 rounded bg-soft-danger">
                                    <h5 class="mb-0 fw-bold text-danger">{{ $rejectedCuti }}</h5>
                                    <small class="smaller text-muted text-uppercase">Ditolak</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </x-card>
        </div>
    </div>

    {{-- Row 4: Main Charts --}}
    <div class="row">
        <div class="col-xxl-6 col-lg-12">
            <x-card full-height class="border-0 shadow-sm premium-card">
                <x-slot:header>
                    <div class="d-flex align-items-center justify-content-between w-100">
                        <h5 class="mb-0 card-title">Tren Kehadiran Siswa (7 Hari Terakhir)</h5>
                        <div class="px-2 border badge rounded-pill bg-soft-success text-success border-success border-opacity-10">Target: 95%</div>
                    </div>
                </x-slot:header>
                <div id="attendanceTrendChart" style="min-height: 350px;"></div>
            </x-card>
        </div>
        <div class="col-xxl-6 col-lg-12">
            <x-card full-height class="border-0 shadow-sm premium-card">
                <x-slot:header>
                    <div class="d-flex align-items-center justify-content-between w-100">
                        <h5 class="mb-0 card-title">Statistik Keuangan (6 Bulan Terakhir)</h5>
                        <div class="dropdown">
                            <a href="javascript:void(0);" class="avatar-text avatar-sm" data-bs-toggle="dropdown">
                                <i class="feather-more-vertical"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a href="javascript:void(0);" class="dropdown-item">
                                    <i class="feather-download me-2"></i> Unduh Laporan
                                </a>
                                <a href="javascript:void(0);" class="dropdown-item">
                                    <i class="feather-printer me-2"></i> Cetak Rekapitulasi
                                </a>
                            </div>
                        </div>
                    </div>
                </x-slot:header>
                <div id="financeTrendChart" style="min-height: 350px;"></div>
            </x-card>
        </div>
    </div>

    {{-- Row 4: Operational Widgets --}}
    <div class="row">
        <div class="col-xxl-4 col-md-6">
            <x-card full-height class="border-0 shadow-sm premium-card">
                <x-slot:header>
                    <div class="d-flex align-items-center justify-content-between w-100">
                        <h5 class="mb-0 card-title">Agenda Terdekat</h5>
                        <a href="{{ route('dashboard.academic-calendar.index') }}" class="small fw-bold">Lihat Semua</a>
                    </div>
                </x-slot:header>
                <div class="p-0 card-body">
                    <div class="border-0 list-group list-group-flush" id="upcoming-events-container">
                        @forelse ($stats['acara_terdekat'] as $event)
                            <div class="gap-3 py-3 border-0 list-group-item d-flex align-items-center">
                                <div class="flex-shrink-0 rounded avatar-text avatar-md bg-soft-info text-info">
                                    <span class="fw-bold">{{ \Carbon\Carbon::parse($event->start_date)->format('d') }}</span>
                                    <span class="small" style="font-size: 8px;">{{ \Carbon\Carbon::parse($event->start_date)->format('M') }}</span>
                                </div>
                                <div class="overflow-hidden">
                                    <span class="text-truncate-1-line fw-bold d-block">{{ $event->title }}</span>
                                    <span class="text-muted small d-block"><i class="feather-map-pin me-1"></i> {{ $event->location ?? 'Sekolah' }}</span>
                                </div>
                            </div>
                        @empty
                            <div class="p-4 text-center">
                                <span class="text-muted small">Tidak ada agenda terdekat</span>
                            </div>
                        @endforelse
                    </div>
                </div>
            </x-card>
        </div>
        <div class="col-xxl-4 col-md-6">
            <x-card full-height class="border-0 shadow-sm premium-card">
                <x-slot:header>
                    <div class="d-flex align-items-center justify-content-between w-100">
                        <h5 class="mb-0 card-title">Peringatan Dini</h5>
                        <span class="px-2 border badge rounded-pill bg-soft-danger text-danger border-danger border-opacity-10"><span id="stat-risk-alerts-count">{{ $stats['peringatan_dini_aktif'] }}</span> Alerta</span>
                    </div>
                </x-slot:header>
                <div class="p-0 card-body">
                    <div class="table-responsive">
                        <table class="table mb-0 table-hover">
                            <tbody id="risk-assessments-container">
                                @forelse ($stats['risiko_siswa'] as $risk)
                                    <tr>
                                        <td class="border-0">
                                            <div class="gap-2 d-flex align-items-center">
                                                <div class="avatar-image avatar-sm">
                                                    <img src="{{ asset('assets/images/avatar/1.png') }}" class="rounded img-fluid">
                                                </div>
                                                <div>
                                                    <span class="fw-bold small d-block">{{ $risk->student->name }}</span>
                                                    <span class="text-muted smaller d-block">{{ $risk->student->classroom->name ?? '-' }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="border-0 text-end">
                                            <span class="badge bg-soft-danger text-danger smaller">{{ strtoupper($risk->risk_level) }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="p-4 text-center border-0">
                                            <span class="text-muted small">Semua siswa dalam kondisi aman</span>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </x-card>
        </div>
        <div class="col-xxl-4 col-md-12">
            <x-card full-height class="border-0 shadow-sm premium-card">
                <x-slot:header>
                    <div class="d-flex align-items-center justify-content-between w-100">
                        <h5 class="mb-0 card-title">Pesan WhatsApp Pending</h5>
                        <a href="{{ route('dashboard.whatsapp-chat.index') }}" class="px-2 border badge rounded-pill bg-soft-warning text-warning border-warning border-opacity-10">
                            <span id="stat-unread-wa">{{ $stats['pesan_wa_belum_dibaca'] }}</span> Unread
                        </a>
                    </div>
                </x-slot:header>
                <div class="gap-4 mb-4 d-flex align-items-center">
                    <div class="rounded avatar-text avatar-xl bg-soft-success text-success">
                        <i class="feather-message-square"></i>
                    </div>
                    <div>
                        <span class="fs-20 fw-bold d-block" id="stat-unread-wa-big">{{ $stats['pesan_wa_belum_dibaca'] }}</span>
                        <span class="text-muted small">Conversations need response</span>
                    </div>
                </div>
                <div class="d-grid">
                    <a href="{{ route('dashboard.whatsapp-chat.index') }}" class="border-opacity-25 btn btn-success border-success w-100 fw-bold btn-quick-action">
                        Buka Layanan Chat <i class="feather-external-link ms-2"></i>
                    </a>
                </div>
            </x-card>
        </div>
    </div>

    {{-- Row 5: Activity Feed & Cache Stats --}}
    <div class="row">
        <div class="col-xxl-8 col-lg-7">
            <x-card full-height class="border-0 shadow-sm">
                <x-slot:header>
                    <div class="d-flex align-items-center justify-content-between w-100">
                        <h5 class="mb-0 card-title">Aktivitas Sistem Terbaru</h5>
                        <a href="{{ route('dashboard.audit_log.index') }}" class="small fw-bold">Log Lengkap</a>
                    </div>
                </x-slot:header>
                <div class="p-0 card-body">
                    <div class="table-responsive">
                        <table class="table mb-0 align-middle table-hover">
                            <thead>
                                <tr class="smaller text-muted text-uppercase fw-bold">
                                    <th class="ps-4">Admin</th>
                                    <th>Aksi</th>
                                    <th>Modul</th>
                                    <th class="text-end pe-4">Waktu</th>
                                </tr>
                            </thead>
                            <tbody id="recent-activities-container">
                                @foreach ($stats['recent_activities'] as $log)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="gap-2 hstack">
                                                <div class="text-white avatar-text avatar-sm bg-primary rounded-circle">
                                                    {{ strtoupper(substr($log->user->name ?? 'S', 0, 1)) }}
                                                </div>
                                                <span class="fw-bold small">{{ $log->user->name ?? 'System' }}</span>
                                            </div>
                                        </td>
                                        <td><span class="small">{{ $log->description }}</span></td>
                                        <td>
                                            <span class="px-2 border badge rounded-pill bg-soft-secondary text-secondary smaller border-secondary border-opacity-10">{{ class_basename($log->model_type) }}</span>
                                        </td>
                                        <td class="text-end pe-4 small text-muted">{{ $log->created_at->diffForHumans() }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </x-card>
        </div>
        <div class="col-xxl-4 col-lg-5">
            <x-card full-height class="border-0 shadow-sm premium-card">
                <x-slot:header>
                    <h5 class="mb-0 card-title">System Health & Cache</h5>
                </x-slot:header>
                <div class="gap-3 mb-4 hstack">
                    <div class="rounded avatar-text avatar-lg bg-soft-info text-info">
                        <i class="feather-database"></i>
                    </div>
                    <div>
                        <span class="text-muted small d-block">Cache Items</span>
                        <span class="fw-bold fs-18" id="cacheItems">{{ number_format($stats['cache_items'] ?? 0) }}</span>
                    </div>
                </div>
                <div class="gap-3 mb-4 hstack">
                    <div class="rounded avatar-text avatar-lg bg-soft-success text-success">
                        <i class="feather-cpu"></i>
                    </div>
                    <div>
                        <span class="text-muted small d-block">Hit Rate</span>
                        <div class="mt-1 progress" style="height: 6px; width: 100px;">
                            <div id="cacheHitRateBar" class="progress-bar bg-success" style="width: {{ $stats['cache_hit_rate'] ?? 0 }}%"></div>
                        </div>
                    </div>
                    <span id="cacheHitRateText" class="ms-auto fw-bold text-success">{{ $stats['cache_hit_rate'] ?? 0 }}%</span>
                </div>

                <hr class="my-4 op-10">

                <div class="mb-3">
                    <div class="small text-muted">Modules</div>
                    <ul id="cacheModuleList" class="mt-2 list-group list-group-flush">
                        @foreach ($stats['cache_modules'] ?? [] as $module => $m)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                {{ ucfirst($module) }}
                                <span class="badge bg-secondary" id="module-count-{{ $module }}">{{ $m['key_count'] ?? 0 }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="gap-2 mb-3 hstack">
                    <i class="feather-clock text-muted small"></i>
                    <span class="text-muted small">Last Refresh: <span id="lastCacheRefresh">{{ $stats['last_cache_refresh'] }}</span></span>
                </div>

                <div class="d-grid">
                    <button type="button" class="btn btn-sm btn-primary" id="clearCacheBtn">
                        <i class="feather-zap me-1"></i> Clear System Cache
                    </button>
                </div>
            </x-card>
        </div>
    </div>
    </div>{{-- end col-12 --}}
@endsection

@section('modal')
    {{-- Any dashboard specific modals can go here --}}
@endsection

@push('scripts')
    <script>
        let attendanceChart, financeChart;

        function refreshDashboardStats() {
            console.log('[Dashboard] Refreshing stats...');
            var filterData = {
                date_from: $('input[name="date_from"]').val(),
                date_to: $('input[name="date_to"]').val(),
                academic_year: $('select[name="academic_year"]').val(),
            };
            $.get('{{ route('dashboard.stats') }}', filterData, function(response) {
                if (response.success) {
                    const s = response.data.stats;
                    const c = response.data.charts;

                    // Update KPI Cards
                    $('#stat-total-students').text(new Intl.NumberFormat().format(s.total_students));
                    $('#stat-total-staff').text(new Intl.NumberFormat().format(s.total_teachers + s.total_employees));
                    $('#stat-attendance-today').text(s.attendance_today);
                    $('#stat-outstanding-payments').text(new Intl.NumberFormat().format(s.outstanding_payments / 1000000).substring(0, 4));
                    $('#stat-my-active-tasks').text(s.tugas_aktif_saya);
                    $('#stat-risk-alerts-count').text(s.peringatan_dini_aktif);
                    $('#stat-unread-wa').text(s.pesan_wa_belum_dibaca);
                    $('#stat-unread-wa-big').text(s.pesan_wa_belum_dibaca);

                    // Update Charts
                    if (attendanceChart) {
                        attendanceChart.updateSeries([
                            { name: 'Hadir', data: c.attendance.hadir },
                            { name: 'Izin', data: c.attendance.izin },
                            { name: 'Sakit', data: c.attendance.sakit },
                            { name: 'Alpa', data: c.attendance.alpha }
                        ]);
                    }

                    if (financeChart) {
                        financeChart.updateSeries([
                            { name: 'Lunas', data: c.finance.income },
                            { name: 'Belum Bayar', data: c.finance.unpaid },
                            { name: 'Gagal/Terlambat', data: c.finance.failed }
                        ]);
                    }

                    // Trigger custom UI updates (recent activities, events, etc.)
                    updateDashboardUI(s);
                }
            });
        }

        function updateDashboardUI(s) {
            // Update Recent Activities
            if (s.recent_activities && s.recent_activities.length > 0) {
                let html = '';
                s.recent_activities.forEach(log => {
                    const initials = (log.user?.name || 'S').substring(0, 1).toUpperCase();
                    html += `
                        <tr>
                            <td class="ps-4">
                                <div class="gap-2 hstack">
                                    <div class="text-white avatar-text avatar-sm bg-primary rounded-circle">${initials}</div>
                                    <span class="fw-bold small">${log.user?.name || 'System'}</span>
                                </div>
                            </td>
                            <td><span class="small">${log.description}</span></td>
                            <td><span class="badge bg-soft-secondary text-secondary smaller">${log.model_type.split('\\').pop()}</span></td>
                            <td class="text-end pe-4 small text-muted">Baru saja</td>
                        </tr>
                    `;
                });
                $('#recent-activities-container').html(html);
            }

            // Update Upcoming Events
            if (s.acara_terdekat && s.acara_terdekat.length > 0) {
                let html = '';
                s.acara_terdekat.forEach(event => {
                    const date = new Date(event.start_date);
                    const day = date.getDate();
                    const month = date.toLocaleString('default', { month: 'short' });
                    html += `
                        <div class="gap-3 py-3 border-0 list-group-item d-flex align-items-center">
                            <div class="flex-shrink-0 rounded avatar-text avatar-md bg-soft-info text-info">
                                <span class="fw-bold">${day}</span>
                                <span class="small" style="font-size: 8px;">${month}</span>
                            </div>
                            <div class="overflow-hidden">
                                <span class="text-truncate-1-line fw-bold d-block">${event.title}</span>
                                <span class="text-muted small d-block"><i class="feather-map-pin me-1"></i> ${event.location || 'Sekolah'}</span>
                            </div>
                        </div>
                    `;
                });
                $('#upcoming-events-container').html(html);
            }

            // Update Risk Assessments
            if (s.risiko_siswa && s.risiko_siswa.length > 0) {
                let html = '';
                s.risiko_siswa.forEach(risk => {
                    html += `
                        <tr>
                            <td class="border-0">
                                <div class="gap-2 d-flex align-items-center">
                                    <div class="avatar-image avatar-sm">
                                        <img src="/assets/images/avatar/1.png" class="rounded img-fluid">
                                    </div>
                                    <div>
                                        <span class="fw-bold small d-block">${risk.student?.name || '-'}</span>
                                        <span class="text-muted smaller d-block">${risk.student?.classroom?.name || '-'}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="border-0 text-end">
                                <span class="badge bg-soft-danger text-danger smaller">${risk.risk_level.toUpperCase()}</span>
                            </td>
                        </tr>
                    `;
                });
                $('#risk-assessments-container').html(html);
            }
        }

        // Global exposing for Echo
        window.refreshDashboardStats = refreshDashboardStats;

        $(document).ready(function() {
            // Notifications for Cache Clear
            $('#clearCacheBtn').on('click', function() {
                window.ConfirmAction(
                    'Kosongkan Cache?',
                    'Hal ini akan menghapus data cache sementara di seluruh sistem. Lanjutkan?',
                    'Ya, Hapus!'
                ).then((result) => {
                    if (result.value) {
                        $.ajax({
                            url: '{{ route('dashboard.cache.flush') }}',
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                window.Toast.fire({
                                    icon: 'success',
                                    title: 'Cache berhasil dikosongkan'
                                });
                                refreshDashboardStats();
                            }
                        });
                    }
                });
            });

            // Attendance Trend Chart
            const attendanceOptions = {
                series: [
                    { name: 'Hadir', data: @json($charts['attendance']['hadir'] ?? []) },
                    { name: 'Izin', data: @json($charts['attendance']['izin'] ?? []) },
                    { name: 'Sakit', data: @json($charts['attendance']['sakit'] ?? []) },
                    { name: 'Alpa', data: @json($charts['attendance']['alpha'] ?? []) }
                ],
                chart: { height: 350, type: 'area', toolbar: { show: false }, zoom: { enabled: false } },
                colors: ['#3454d1', '#ffa500', '#2ecc71', '#e74c3c'],
                dataLabels: { enabled: false },
                stroke: { curve: 'smooth', width: 2 },
                xaxis: { categories: @json($charts['attendance']['labels'] ?? []) },
                yaxis: { title: { text: 'Jumlah Siswa' } },
                fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.05 } },
                legend: { position: 'top', horizontalAlign: 'right' }
            };
            attendanceChart = new ApexCharts(document.querySelector("#attendanceTrendChart"), attendanceOptions);
            attendanceChart.render();

            // Finance Trend Chart
            const financeOptions = {
                series: [
                    { name: 'Lunas', data: @json($charts['finance']['income'] ?? []) },
                    { name: 'Belum Bayar', data: @json($charts['finance']['unpaid'] ?? []) },
                    { name: 'Gagal/Terlambat', data: @json($charts['finance']['failed'] ?? []) }
                ],
                chart: { height: 350, type: 'bar', stacked: true, toolbar: { show: false } },
                colors: ['#3454d1', '#ffa500', '#e74c3c'],
                plotOptions: { bar: { horizontal: false, columnWidth: '35%', borderRadius: 4, dataLabels: { position: 'top' } } },
                xaxis: { categories: @json($charts['finance']['labels'] ?? []) },
                yaxis: { labels: { formatter: function(val) { return "Rp " + (val / 1000000).toFixed(1) + "jt" } } },
                legend: { position: 'top', horizontalAlign: 'right' },
                tooltip: { y: { formatter: function(val) { return "Rp " + val.toLocaleString('id-ID') } } }
            };
            financeChart = new ApexCharts(document.querySelector("#financeTrendChart"), financeOptions);
            financeChart.render();

            // Gender Distribution Chart
            const genderOptions = {
                series: @json($stats['gender_distribution']->pluck('total')),
                chart: { type: 'donut', height: 180 },
                labels: @json($stats['gender_distribution']->pluck('gender')),
                colors: ['#3454d1', '#e74c3c'],
                legend: { show: false },
                dataLabels: { enabled: false },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '70%',
                            labels: {
                                show: true,
                                total: {
                                    show: true,
                                    label: 'Total',
                                    formatter: () => '{{ $stats['total_students'] }}'
                                }
                            }
                        }
                    }
                }
            };
            new ApexCharts(document.querySelector("#genderPieChart"), genderOptions).render();
        });
    </script>
@endpush
