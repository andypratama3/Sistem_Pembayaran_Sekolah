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
                title="Total Kelas"
                :value="$stats['total_classrooms']"
                icon="home"
                iconBg="bg-soft-info"
                iconColor="text-info"
                id="stat-total-classrooms"
                route="{{ route('dashboard.classrooms.index') }}"
                routeLabel="<i class='feather-box fs-10 me-1'></i> Kelola Kelas"
            />
            <x-analytics-card
                title="Tagihan Pending"
                :value="$stats['outstanding_payments']"
                icon="dollar-sign"
                iconBg="bg-soft-danger"
                iconColor="text-danger"
                format="currency"
                :divisor="1000000"
                :decimal="1"
                suffix="jt"
                id="stat-outstanding-payments"
                badge="Tertunda"
                badgeClass="badge bg-soft-danger text-danger"
                route="{{ route('dashboard.payments.index') }}"
                routeLabel="<i class='feather-credit-card fs-10 me-1'></i> Kelola Tagihan"
            />
            <x-analytics-card
                title="Masuk Bulan Ini"
                :value="$stats['total_payments_month']"
                icon="trending-up"
                iconBg="bg-soft-success"
                iconColor="text-success"
                format="currency"
                :divisor="1000000"
                :decimal="1"
                suffix="jt"
                id="stat-total-payments-month"
                badge="Lunas"
                badgeClass="badge bg-soft-success text-success"
                route="{{ route('dashboard.payments.index') }}"
                routeLabel="<i class='feather-file-text fs-10 me-1'></i> Laporan"
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
                        <a href="{{ route('dashboard.payments.create') }}" class="py-3 btn btn-quick-action w-100 flex-column">
                            <i class="mb-2 feather-credit-card fs-20 text-primary"></i>
                            <span class="fw-bold small text-uppercase">Input Bayar</span>
                        </a>
                    </div>
                    <div class="col-sm-4 col-6">
                        <a href="{{ route('dashboard.students.create') }}" class="py-3 btn btn-quick-action w-100 flex-column">
                            <i class="mb-2 feather-user-plus fs-20 text-success"></i>
                            <span class="fw-bold small text-uppercase">Tambah Siswa</span>
                        </a>
                    </div>
                    <div class="col-sm-4 col-6">
                        <a href="{{ route('dashboard.whatsapp-chat.index') }}" class="py-3 btn btn-quick-action w-100 flex-column">
                            <i class="mb-2 feather-message-circle fs-20 text-info"></i>
                            <span class="fw-bold small text-uppercase">Kirim Pesan</span>
                        </a>
                    </div>
                    <div class="col-sm-4 col-6">
                        <a href="{{ route('dashboard.classrooms.index') }}" class="py-3 btn btn-quick-action w-100 flex-column">
                            <i class="mb-2 feather-home fs-20 text-warning"></i>
                            <span class="fw-bold small text-uppercase">Data Kelas</span>
                        </a>
                    </div>
                    <div class="col-sm-4 col-6">
                        <a href="{{ route('dashboard.audit_log.index') }}" class="py-3 btn btn-quick-action w-100 flex-column">
                            <i class="mb-2 feather-list fs-20 text-danger"></i>
                            <span class="fw-bold small text-uppercase">Log Sistem</span>
                        </a>
                    </div>
                    <div class="col-sm-4 col-6">
                        <a href="{{ route('dashboard.settings.users.index') }}" class="py-3 btn btn-quick-action w-100 flex-column">
                            <i class="mb-2 feather-users fs-20 text-secondary"></i>
                            <span class="fw-bold small text-uppercase">Kelola User</span>
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
                    <p class="text-white text-opacity-75 small">Pantau aktivitas siswa dan verifikasi pembayaran untuk menutup hari.</p>

                    <div class="gap-3 pt-4 mt-4 border-white hstack border-top border-opacity-10">
                        <div>
                            <span class="text-white text-opacity-50 d-block small">Tahun Ajaran</span>
                            <span class="text-white fw-bold">{{ $stats['academic_years']->where('is_active', true)->first()->name ?? 'N/A' }}</span>
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
                        <h5 class="mb-0 card-title">Demografi Siswa</h5>
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
                                <div class="p-2 rounded bg-body-secondary border-start border-3 border-{{ $status == 'aktif' ? 'success' : 'secondary' }}">
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
                        <h5 class="mb-0 card-title">Statistik SDM</h5>
                        <i class="feather-users text-success"></i>
                    </div>
                </x-slot:header>
                <div class="gap-3 d-flex flex-column">
                    <div class="p-3 rounded bg-soft-primary border-0 d-flex align-items-center">
                        <div class="avatar-text avatar-md bg-primary text-white rounded me-3">
                            <i class="feather-user"></i>
                        </div>
                        <div>
                            <span class="text-muted small d-block">Total Guru</span>
                            <span class="fs-18 fw-bold" id="stat-total-teachers">{{ number_format($stats['total_teachers']) }}</span>
                        </div>
                    </div>
                    <div class="p-3 rounded bg-soft-info border-0 d-flex align-items-center">
                        <div class="avatar-text avatar-md bg-info text-white rounded me-3">
                            <i class="feather-briefcase"></i>
                        </div>
                        <div>
                            <span class="text-muted small d-block">Total Pegawai</span>
                            <span class="fs-18 fw-bold" id="stat-total-employees">{{ number_format($stats['total_employees']) }}</span>
                        </div>
                    </div>
                </div>
            </x-card>
        </div>
        <div class="col-xxl-4 col-lg-12">
            <x-card full-height class="border-0 shadow-sm premium-card">
                <x-slot:header>
                    <div class="d-flex align-items-center justify-content-between w-100">
                        <h5 class="mb-0 card-title">Ulang Tahun Siswa</h5>
                        <i class="feather-gift text-danger"></i>
                    </div>
                </x-slot:header>
                <div class="list-group list-group-flush">
                    @forelse($stats['student_birthdays'] as $birthdayStudent)
                        <div class="px-0 border-0 list-group-item d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <div class="avatar-text avatar-sm bg-soft-danger text-danger rounded-circle me-2">
                                    {{ strtoupper(substr($birthdayStudent->name, 0, 1)) }}
                                </div>
                                <div>
                                    <span class="fw-bold small d-block">{{ $birthdayStudent->name }}</span>
                                    <span class="text-muted smaller">{{ $birthdayStudent->birth_date->format('d M') }}</span>
                                </div>
                            </div>
                            @if($birthdayStudent->birth_date->format('m-d') == now()->format('m-d'))
                                <span class="badge bg-soft-danger text-danger rounded-pill smaller">Hari Ini!</span>
                            @endif
                        </div>
                    @empty
                        <div class="py-4 text-center text-muted small">Tidak ada ulang tahun dalam 14 hari ke depan.</div>
                    @endforelse
                </div>
            </x-card>
        </div>
    </div>

    {{-- Row 4: Main Charts --}}
    <div class="row">
        <div class="col-xxl-12 col-lg-12">
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
        <div class="col-xxl-8 col-lg-7">
            <x-card full-height class="border-0 shadow-sm premium-card">
                <x-slot:header>
                    <div class="d-flex align-items-center justify-content-between w-100">
                        <h5 class="mb-0 card-title">Pembayaran Terbaru</h5>
                        <a href="{{ route('dashboard.payments.index') }}" class="small fw-bold">Semua Transaksi</a>
                    </div>
                </x-slot:header>
                <div class="p-0 card-body">
                    <div class="table-responsive">
                        <table class="table mb-0 align-middle table-hover">
                            <thead>
                                <tr class="smaller text-muted text-uppercase fw-bold">
                                    <th class="ps-4">Siswa</th>
                                    <th>Kategori</th>
                                    <th>Nominal</th>
                                    <th>Status</th>
                                    <th class="text-end pe-4">Tanggal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($stats['recent_payments'] as $payment)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="gap-2 hstack">
                                                <div class="text-white avatar-text avatar-sm bg-soft-primary text-primary rounded-circle">
                                                    {{ strtoupper(substr($payment->student->name ?? 'S', 0, 1)) }}
                                                </div>
                                                <div>
                                                    <span class="fw-bold small d-block">{{ $payment->student->name ?? 'N/A' }}</span>
                                                    <span class="text-muted smaller">#{{ $payment->order_id }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td><span class="small">{{ $payment->paymentTitle->name ?? 'N/A' }}</span></td>
                                        <td><span class="fw-bold small">Rp {{ number_format($payment->gross_amount, 0, ',', '.') }}</span></td>
                                        <td>
                                            @php
                                                $statusClass = match($payment->status) {
                                                    'paid', 'completed' => 'success',
                                                    'pending' => 'warning',
                                                    'failed', 'expired', 'deny' => 'danger',
                                                    default => 'secondary'
                                                };
                                            @endphp
                                            <span class="px-2 border badge rounded-pill bg-soft-{{ $statusClass }} text-{{ $statusClass }} smaller border-{{ $statusClass }} border-opacity-10">{{ strtoupper($payment->status) }}</span>
                                        </td>
                                        <td class="text-end pe-4 small text-muted">{{ $payment->created_at->format('d/m/Y') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-4 text-center text-muted small">Belum ada data pembayaran</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </x-card>
        </div>

        <div class="col-xxl-4 col-lg-5">
            <x-card full-height class="border-0 shadow-sm premium-card">
                <x-slot:header>
                    <div class="d-flex align-items-center justify-content-between w-100">
                        <h5 class="mb-0 card-title">WhatsApp Pending</h5>
                        <a href="{{ route('dashboard.whatsapp-chat.index') }}" class="px-2 border badge rounded-pill bg-soft-warning text-warning border-warning border-opacity-10">
                            <span id="stat-unread-wa">{{ $stats['pesan_wa_belum_dibaca'] }}</span> Unread
                        </a>
                    </div>
                </x-slot:header>
                <div class="gap-4 mb-4 d-flex align-items-center">
                    <div class="rounded-circle avatar-text avatar-xl bg-soft-success text-success" style="width: 60px; height: 60px;">
                        <i class="feather-message-square fs-24"></i>
                    </div>
                    <div>
                        <span class="fs-28 fw-bold d-block" id="stat-unread-wa-big">{{ $stats['pesan_wa_belum_dibaca'] }}</span>
                        <span class="text-muted small">Percakapan butuh respon</span>
                    </div>
                </div>
                <div class="mb-3 alert alert-soft-info border-0 small">
                    <i class="feather-info me-2"></i> Balas pesan wali murid tepat waktu untuk meningkatkan pelayanan.
                </div>
                <div class="d-grid">
                    <a href="{{ route('dashboard.whatsapp-chat.index') }}" class="btn btn-premium w-100 fw-bold">
                        Buka Layanan Chat <i class="feather-external-link ms-2"></i>
                    </a>
                </div>
            </x-card>
        </div>
    </div>

    {{-- Row 5: Activity Feed & Cache Stats --}}
    <div class="row">
        <div class="col-xxl-8 col-lg-7">
            <x-card full-height class="border-0 shadow-sm premium-card">
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
                    <div class="d-flex align-items-center justify-content-between w-100">
                        <h5 class="mb-0 card-title">Status Cache</h5>
                        <i class="feather-database text-primary"></i>
                    </div>
                </x-slot:header>
                <div class="mb-4 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small d-block">Sistem Cache</span>
                        <span class="fw-bold text-success"><i class="feather-check-circle me-1"></i> Aktif (Redis)</span>
                    </div>
                    <form action="{{ route('dashboard.cache.flush') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-soft-danger">
                            <i class="feather-trash-2 me-1"></i> Kosongkan
                        </button>
                    </form>
                </div>
                <div class="gap-3 d-flex flex-column">
                    <div class="p-3 rounded bg-body-secondary border-start border-3 border-primary">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="smaller text-muted text-uppercase fw-bold">Waktu Refresh Terakhir</div>
                                <div class="fw-bold">{{ now()->format('H:i:s') }}</div>
                            </div>
                            <i class="feather-clock text-muted"></i>
                        </div>
                    </div>
                    <div class="p-3 rounded bg-body-secondary border-start border-3 border-info">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="smaller text-muted text-uppercase fw-bold">Optimasi Database</div>
                                <div class="fw-bold">Teroptimasi</div>
                            </div>
                            <i class="feather-zap text-muted"></i>
                        </div>
                    </div>
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
        let financeChart;

        function refreshDashboardStats() {
            var filterData = {
                date_from: $('input[name="date_from"]').val(),
                date_to: $('input[name="date_to"]').val(),
                academic_year: $('select[name="academic_year"]').val(),
            };
            $.get('{{ route('dashboard.stats') }}', filterData, function(response) {
                if (response.success) {
                    const s = response.data.stats;
                    const c = response.data.charts;

                    $('#stat-total-students').text(new Intl.NumberFormat().format(s.total_students));
                    $('#stat-total-classrooms').text(new Intl.NumberFormat().format(s.total_classrooms));
                    $('#stat-total-teachers').text(new Intl.NumberFormat().format(s.total_teachers));
                    $('#stat-total-employees').text(new Intl.NumberFormat().format(s.total_employees));
                    $('#stat-outstanding-payments').text(new Intl.NumberFormat('id-ID', { minimumFractionDigits: 1 }).format(s.outstanding_payments / 1000000) + 'jt');
                    $('#stat-total-payments-month').text(new Intl.NumberFormat('id-ID', { minimumFractionDigits: 1 }).format(s.total_payments_month / 1000000) + 'jt');
                    $('#stat-unread-wa').text(s.pesan_wa_belum_dibaca);
                    $('#stat-unread-wa-big').text(s.pesan_wa_belum_dibaca);

                    if (financeChart) {
                        financeChart.updateSeries([
                            { name: 'Lunas', data: c.finance.income },
                            { name: 'Belum Bayar', data: c.finance.unpaid },
                            { name: 'Gagal/Terlambat', data: c.finance.failed }
                        ]);
                    }

                    updateRecentActivities(s);
                }
            });
        }

        function updateRecentActivities(s) {
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
        }

        window.refreshDashboardStats = refreshDashboardStats;

        $(document).ready(function() {
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
