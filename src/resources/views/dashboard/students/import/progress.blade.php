@extends('layouts.app')

@section('title', 'Import Progress — ' . substr($batchId, 0, 8))

@section('page-header')
    <x-page-header>
        <x-slot:left>
            {{ Breadcrumbs::render('dashboard.students.index') }}
        </x-slot:left>
        <x-slot:actions>
             <a href="{{ route('dashboard.students.index') }}" class="px-4 shadow-sm btn btn-light-brand rounded-pill">
                <i class="feather-arrow-left me-2"></i>
                <span>Kembali ke Daftar</span>
            </a>
        </x-slot:actions>
    </x-page-header>
@endsection

@section('content')
    <div class="col-12">
        {{-- Header Status Card --}}
        <div class="mb-4 overflow-hidden border-0 shadow-sm card rounded-4">
            <div class="p-4 card-body">
                <div class="flex-wrap gap-3 d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <div class="border border-2 border-white shadow-sm avatar-text avatar-lg bg-soft-primary text-primary rounded-circle me-3">
                            <i class="feather-upload-cloud"></i>
                        </div>
                        <div>
                            <h4 class="mb-1 fw-bolder text-dark">Sinkronisasi Data Siswa</h4>
                            <div class="flex-wrap gap-2 d-flex align-items-center">
                                <span class="px-2 py-1 border badge text-dark font-monospace fw-bold">#{{ substr($batchId, 0, 8) }}</span>
                                <span class="text-muted small fw-medium"><i class="feather-clock me-1 text-primary"></i> Dimulai: {{ now()->format('H:i:s') }}</span>
                                <span class="text-muted small fw-medium"><i class="feather-user me-1 text-primary"></i> Operator: {{ auth()->user()->name }}</span>
                            </div>
                        </div>
                    </div>
                    <div id="batchStatusBadge">
                        @if(($initialData['is_finished'] ?? false))
                            <div class="px-4 py-2 border shadow-sm d-flex align-items-center bg-soft-success text-success rounded-pill fw-bold border-success border-opacity-10">
                                <i class="feather-check-circle me-2 fs-5"></i> Selesai
                            </div>
                        @else
                            <div class="px-4 py-2 border border-opacity-25 shadow-sm d-flex align-items-center bg-soft-primary text-primary rounded-pill fw-bold border-primary pulse-blue">
                                <i class="feather-refresh-cw me-2 fs-5 spin"></i> Memproses Antrean...
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            {{-- Left Column: Main Progress & List --}}
            <div class="col-lg-8">
                {{-- Progress Dashboard --}}
                <div class="mb-4 overflow-hidden border-0 shadow-sm card rounded-4">
                    <div class="p-4 pt-5 pb-5 card-body">
                        <div id="loadingState" class="py-5 text-center" style="{{ ($initialData['progress'] ?? 0) > 0 ? 'display:none;' : '' }}">
                            <div class="mb-4 loader-container">
                                <div class="spinner-border text-primary" style="width: 4rem; height: 4rem; border-width: 0.25em;" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                            <h3 class="mb-2 fw-black text-dark">Menyiapkan Sinkronisasi...</h3>
                            <p class="mx-auto text-muted fs-16 max-w-500">Sistem sedang menganalisis file dan membagi data ke dalam beberapa antrean untuk performa maksimal.</p>
                        </div>

                        <div id="progressState" style="{{ ($initialData['progress'] ?? 0) > 0 && !($initialData['is_finished'] ?? false) ? '' : 'display:none;' }}">
                            <div class="mb-5 text-center">
                                <div class="mb-0 display-1 fw-black text-primary ls-n2 lh-1" id="importPercentage">{{ round($initialData['progress'] ?? 0) }}%</div>
                                <div id="currentStatus" class="px-3 py-2 mt-3 shadow-sm badge bg-soft-primary text-primary rounded-pill text-uppercase ls-1 small fw-bold">
                                    Menghubungkan ke Stream...
                                </div>
                            </div>

                            <div class="px-md-5">
                                <div class="p-1 mb-4 border shadow-sm progress rounded-pill" style="height: 28px;">
                                    <div id="importProgressBar" class="shadow-sm progress-bar progress-bar-striped progress-bar-animated bg-primary rounded-pill"
                                         role="progressbar" style="width: {{ $initialData['progress'] ?? 0 }}%;">
                                    </div>
                                </div>

                                <div class="mt-2 text-center row g-3">
                                    <div class="col-4">
                                        <div class="p-3 transition-all border shadow-sm rounded-4 hover-shadow">
                                            <div class="mb-1 text-muted small text-uppercase fw-bold ls-1">Berhasil</div>
                                            <div class="mb-0 h3 fw-black text-success" id="importCountSuccess">{{ $initialData['cached']['success'] ?? 0 }}</div>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="p-3 transition-all border shadow-sm rounded-4 hover-shadow">
                                            <div class="mb-1 text-muted small text-uppercase fw-bold ls-1">Masalah</div>
                                            <div class="mb-0 h3 fw-black text-danger" id="importCountFailed">{{ $initialData['cached']['failed'] ?? 0 }}</div>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="p-3 transition-all border shadow-sm rounded-4 hover-shadow">
                                            <div class="mb-1 text-muted small text-uppercase fw-bold ls-1">Total Target</div>
                                            <div class="mb-0 h3 fw-black text-dark" id="importCountTotal">{{ $totalRows }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Finish State Card --}}
                        <div id="finishState" style="{{ ($initialData['is_finished'] ?? false) ? '' : 'display: none;' }}" class="py-4 text-center">
                            <div class="mb-4 celebration-container position-relative">
                                <div class="mx-auto border border-4 border-white shadow-lg avatar-text avatar-xl bg-soft-success text-success rounded-circle pulse-animation" style="width: 140px; height: 140px;">
                                    <i class="feather-check-circle fs-1"></i>
                                </div>
                                <div class="confetti-anchor"></div>
                            </div>
                            <h1 class="mb-3 fw-black text-dark display-5 ls-n1">Impor Selesai!</h1>
                            <div class="p-4 mx-auto mb-5 border border-2 border-dashed max-w-500 rounded-4">
                                <p class="mb-0 text-dark fs-18" id="importSummaryText">
                                    Berhasil menyinkronkan <strong>{{ $initialData['cached']['success'] ?? 0 }}</strong> data siswa ke dalam database sekolah.
                                    @if(($initialData['cached']['failed'] ?? 0) > 0)
                                        <div class="mt-2 text-danger fw-bold small"><i class="feather-alert-octagon me-1"></i> Terdapat {{ $initialData['cached']['failed'] ?? 0 }} data yang dilewati karena kesalahan format.</div>
                                    @endif
                                </p>
                            </div>
                            <div class="flex-wrap gap-3 d-flex justify-content-center">
                                <a href="{{ route('dashboard.students.index') }}" class="px-5 py-3 shadow-lg btn btn-primary rounded-pill fw-black transform-hover">
                                    <i class="feather-users me-2"></i> Lihat Database Siswa
                                </a>
                                <button type="button" class="px-5 py-3 border shadow-sm btn btn-white rounded-pill fw-bold transform-hover" onclick="location.reload()">
                                    <i class="feather-list me-2"></i> Rincian Lengkap
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Real-time Feed & Issue Tabs --}}
                <div class="overflow-hidden border-0 shadow-sm card rounded-4">
                    <div class="p-0 bg-transparent card-header border-bottom">
                        <ul class="nav nav-pills custom-pills-modern" id="importResultTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="success-tab" data-bs-toggle="tab" data-bs-target="#successList" type="button" role="tab">
                                    <i class="feather-activity me-2"></i>Aktivitas Terbaru
                                    <span class="badge bg-success ms-2 shadow-sm px-2" id="badgeCountSuccess">0</span>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="failed-tab" data-bs-toggle="tab" data-bs-target="#failedList" type="button" role="tab">
                                    <i class="feather-alert-triangle me-2"></i>Log Masalah
                                    <span class="badge bg-danger ms-2 shadow-sm px-2" id="badgeCountFailed">0</span>
                                </button>
                            </li>
                        </ul>
                    </div>
                    <div class="p-0 card-body">
                        <div class="tab-content" id="importResultTabsContent">
                            <div class="tab-pane fade show active" id="successList" role="tabpanel">
                                <div class="table-responsive" style="max-height: 500px; min-height: 350px;">
                                    <table class="table mb-0 align-middle table-hover sticky-header" id="successTable">
                                        <thead class="bg-body-secondary">
                                            <tr>
                                                <th class="ps-4" style="width: 70px;">Row</th>
                                                <th class="text-dark">Siswa</th>
                                                <th class="text-dark">NISN</th>
                                                <th class="text-dark">JK</th>
                                                <th class="text-dark">Rombel</th>
                                                <th class="text-center pe-4 text-dark">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody id="successTableBody">
                                            @if(!empty($initialData['imported_students']))
                                                @foreach(array_reverse($initialData['imported_students']) as $index => $s)
                                                    <tr class="border-4 fade-in border-start border-success">
                                                        <td class="ps-4 text-muted small fw-black">{{ count($initialData['imported_students']) - $index }}</td>
                                                        <td>
                                                            <div class="fw-bold text-dark">{{ $s['name'] }}</div>
                                                            <div class="small text-muted op-80 fw-medium">Sinkronisasi OK</div>
                                                        </td>
                                                        <td><code class="text-primary fw-bold">{{ $s['nisn'] }}</code></td>
                                                        <td><span class="border badge bg-soft-secondary text-dark fw-bold">{{ $s['gender'] == 'Laki-laki' ? 'L' : 'P' }}</span></td>
                                                        <td><span class="fw-black text-dark">{{ $s['rombel'] }}</span></td>
                                                        <td class="text-center pe-4">
                                                            <div class="mx-auto avatar-text avatar-xs bg-soft-success text-success rounded-circle">
                                                                <i class="feather-check"></i>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @else
                                                <tr class="empty-row">
                                                    <td colspan="6" class="py-5 text-center text-muted">
                                                        <div class="mb-3"><i class="feather-inbox fs-1 op-20"></i></div>
                                                        <p class="mb-0 fw-bold text-dark">Menunggu data masuk...</p>
                                                        <small class="text-muted fw-medium">Aktivitas sinkronisasi akan muncul di sini secara real-time</small>
                                                    </td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="failedList" role="tabpanel">
                                <div class="table-responsive" style="max-height: 500px; min-height: 350px;">
                                    <table class="table mb-0 align-middle table-hover sticky-header" id="failedTable">
                                        <thead>
                                            <tr>
                                                <th class="ps-4 text-danger" style="width: 70px;">Row</th>
                                                <th class="text-danger">Siswa / NISN</th>
                                                <th class="text-danger">Pesan Kesalahan</th>
                                                <th class="text-center pe-4 text-danger">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody id="failedTableBody">
                                            @if(!empty($initialData['cached']['errors']))
                                                @foreach(array_reverse($initialData['cached']['errors']) as $index => $error)
                                                    <tr class="border-4 fade-in border-start border-danger">
                                                        <td class="ps-4 text-muted small fw-black">{{ count($initialData['cached']['errors']) - $index }}</td>
                                                        <td>
                                                            <div class="fw-bold text-dark">{{ $error['name'] ?? 'Unknown' }}</div>
                                                            <div class="small text-muted fw-medium">NISN: {{ $error['nisn'] ?? '-' }}</div>
                                                        </td>
                                                        <td>
                                                            <div class="p-2 border border-opacity-25 rounded shadow-sm bg-soft-danger text-danger small border-danger fw-medium">
                                                                {{ $error['error'] ?? 'Unknown error occurred' }}
                                                            </div>
                                                        </td>
                                                        <td class="text-center pe-4">
                                                            <button class="btn btn-xs btn-outline-danger rounded-pill fw-bold" onclick="Swal.fire({title: 'Detail Error', text: '{{ addslashes($error['error'] ?? '') }}', icon: 'error', confirmButtonColor: '#2563eb'})">
                                                                Detail
                                                            </button>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @else
                                                <tr class="empty-row">
                                                    <td colspan="4" class="py-5 text-center text-muted">
                                                        <div class="mb-3"><i class="feather-shield-check fs-1 text-success op-20"></i></div>
                                                        <p class="mb-0 fw-bold text-dark">Belum ada masalah terdeteksi</p>
                                                        <small class="text-muted fw-medium">Semua baris data sejauh ini diproses dengan lancar</small>
                                                    </td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column: Information & Actions --}}
            <div class="col-lg-4">
                {{-- Live Tracker Card --}}
                <div class="mb-4 overflow-hidden border-0 shadow-sm card rounded-4 bg-primary position-relative">
                    <div class="p-4 card-body position-relative z-1">
                        <div class="mb-4 d-flex justify-content-between align-items-center">
                            <div class="fw-black text-uppercase ls-1 small op-90">Stream Status</div>
                            <div class="px-3 py-1 d-flex align-items-center bg-opacity-20 rounded-pill">
                                <span class="pulse-white-ring me-2"></span>
                                <span class="small fw-black" id="connectionStatus">TERHUBUNG</span>
                            </div>
                        </div>
                        <h5 class="mb-1 fw-black">Antrean Real-time</h5>
                        <p class="mb-4 small op-80 fw-medium">Data sedang disiarkan langsung dari worker server.</p>

                        <div class="gap-3 vstack">
                            {{-- Fix for invisible text: use explicit styles and darker backgrounds if needed --}}
                            <div class="p-3 rounded-4 d-flex align-items-center" style="background: rgba(0, 0, 0, 0.15); border: 1px solid rgba(255, 255, 255, 0.1);">
                                <div class="shadow-sm avatar-text avatar-sm text-primary rounded-circle me-3">
                                    <i class="feather-database"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="mb-0 text-white small op-70 fw-bold">Engine</div>
                                    <div class="fw-black">Laravel Batch & Redis</div>
                                </div>
                            </div>
                            <div class="p-3 rounded-4 d-flex align-items-center" style="background: rgba(0, 0, 0, 0.15); border: 1px solid rgba(255, 255, 255, 0.1);">
                                <div class="shadow-sm avatar-text avatar-sm text-primary rounded-circle me-3">
                                    <i class="feather-zap"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="mb-0 small op-70 fw-bold">Protokol</div>
                                    <div class="fw-black">WebSocket (Reverb)</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bottom-0 p-3 position-absolute end-0 op-10">
                        <i class="feather-wifi fs-huge"></i>
                    </div>
                </div>

                {{-- Action Card --}}
                <div class="mb-4 overflow-hidden border-0 shadow-sm card rounded-4">
                    <div class="p-4 text-center card-body">
                        <h6 class="mb-3 fw-black text-dark text-uppercase ls-1 small">Kontrol Sesi</h6>
                        <div class="gap-2 d-grid">
                            <button type="button" class="py-2 shadow-sm btn btn-danger rounded-pill fw-black transform-hover" id="cancelBtn">
                                <i class="feather-x-circle me-2"></i> BATALKAN IMPORT
                            </button>
                            <div class="p-3 mt-2 border rounded-3">
                                <p class="mb-0 text-muted small fw-medium">
                                    <i class="feather-info me-1 text-primary"></i> Data yang sudah berhasil masuk tetap tersimpan meskipun proses dihentikan paksa.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Help Card --}}
                <div class="overflow-hidden border-0 shadow-sm card rounded-4">
                    <div class="p-4 card-body">
                        <h6 class="mb-3 fw-black text-dark d-flex align-items-center">
                            <i class="feather-help-circle text-primary me-2"></i> INFORMASI PROSES
                        </h6>
                        <div class="gap-3 vstack">
                            <div class="gap-3 d-flex">
                                <div class="flex-shrink-0 avatar-text avatar-xs bg-soft-success text-success rounded-circle">
                                    <i class="feather-check"></i>
                                </div>
                                <p class="mb-0 small text-muted fw-medium">Progress bar menunjukkan persentase baris yang sudah diproses secara keseluruhan.</p>
                            </div>
                            <div class="gap-3 d-flex">
                                <div class="flex-shrink-0 avatar-text avatar-xs bg-soft-primary text-primary rounded-circle">
                                    <i class="feather-eye"></i>
                                </div>
                                <p class="mb-0 small text-muted fw-medium">Tab "Aktivitas" menampilkan 100 baris terbaru yang berhasil disinkronkan.</p>
                            </div>
                            <div class="gap-3 d-flex">
                                <div class="flex-shrink-0 avatar-text avatar-xs bg-soft-warning text-warning rounded-circle">
                                    <i class="feather-clock"></i>
                                </div>
                                <p class="mb-0 small text-muted fw-medium">Anda boleh meninggalkan halaman ini; sistem akan mengirim notifikasi saat selesai.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('css')
<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
    }

    .fw-black { font-weight: 900 !important; }
    .ls-n2 { letter-spacing: -3px; }
    .ls-n1 { letter-spacing: -1px; }
    .ls-1 { letter-spacing: 1px; }
    .op-20 { opacity: 0.2; }
    .op-70 { opacity: 0.7; }
    .op-80 { opacity: 0.8; }
    .op-90 { opacity: 0.9; }
    .op-10 { opacity: 0.1; }
    .fs-huge { font-size: 8rem; }

    .spin { animation: fa-spin 2s infinite linear; }
    @keyframes fa-spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(359deg); } }

    .fade-in { animation: fadeIn 0.4s ease-out; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }

    .pulse-blue { animation: pulseBlue 2s infinite; }
    @keyframes pulseBlue { 0% { box-shadow: 0 0 0 0 rgba(37, 99, 235, 0.4); } 70% { box-shadow: 0 0 0 10px rgba(37, 99, 235, 0); } 100% { box-shadow: 0 0 0 0 rgba(37, 99, 235, 0); } }

    .pulse-animation { animation: pulseGreen 2s infinite; }
    @keyframes pulseGreen { 0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.4); } 70% { box-shadow: 0 0 0 20px rgba(16, 185, 129, 0); } 100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); } }

    .pulse-white-ring { width: 8px; height: 8px; background: #fff; border-radius: 50%; box-shadow: 0 0 0 0 rgba(255, 255, 255, 0.7); animation: pulseWhite 1.5s infinite; }
    @keyframes pulseWhite { 0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(255, 255, 255, 0.7); } 70% { transform: scale(1); box-shadow: 0 0 0 8px rgba(255, 255, 255, 0); } 100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(255, 255, 255, 0); } }

    .bg-soft-primary { background-color: rgba(37, 99, 235, 0.12) !important; }
    .bg-soft-success { background-color: rgba(16, 185, 129, 0.12) !important; }
    .bg-soft-danger { background-color: rgba(239, 68, 68, 0.12) !important; }

    .custom-pills-modern {
        display: flex;
        gap: 6px;
        padding: 6px;
        background: #f1f5f9;
        border-bottom: 1px solid #e2e8f0;
    }
    .custom-pills-modern .nav-item { flex: 1; }
    .custom-pills-modern .nav-link {
        width: 100%;
        padding: 10px 16px;
        color: #64748b;
        font-weight: 700;
        font-size: 0.85rem;
        transition: all 0.25s ease;
        border: 1px solid transparent;
        border-radius: 8px;
        text-align: center;
    }
    .custom-pills-modern .nav-link:hover { color: #1e40af; background: rgba(37, 99, 235, 0.06); }
    .custom-pills-modern .nav-link.active {
        background: #fff !important;
        color: #1e293b !important;
        border-color: #e2e8f0 !important;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04) !important;
    }

    .table thead th {
        font-weight: 900;
        text-transform: uppercase;
        font-size: 11px;
        letter-spacing: 1px;
        padding-top: 18px;
        padding-bottom: 18px;
        border-bottom: 2px solid #edf2f7;
    }


    .transform-hover { transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1); }
    .transform-hover:hover { transform: translateY(-4px); }

    .hover-shadow:hover { box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1) !important; }
    .transition-all { transition: all 0.3s ease; }

    .avatar-text.avatar-xs { width: 26px; height: 26px; font-size: 11px; font-weight: 800; }

    .max-w-500 { max-width: 500px; }

    /* Ensure display text is readable */
    .display-1 { font-size: 6rem; line-height: 1; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
<script>
    const ImportManager = {
        batchId: @json($batchId),
        importId: @json($importId),
        userId: @json(auth()->id()),
        totalRows: @json($totalRows),

        successData: @json($initialData['imported_students'] ?? []),
        failedData: @json($initialData['cached']['errors'] ?? []),

        els: {},
        pollInterval: null,
        echoConnected: false,

        init() {
            this.els = {
                loading: document.getElementById('loadingState'),
                progress: document.getElementById('progressState'),
                finish: document.getElementById('finishState'),
                cancelBtn: document.getElementById('cancelBtn'),
                badgeStatus: document.getElementById('batchStatusBadge'),
                progressBar: document.getElementById('importProgressBar'),
                percentage: document.getElementById('importPercentage'),
                countSuccess: document.getElementById('importCountSuccess'),
                countFailed: document.getElementById('importCountFailed'),
                badgeSuccess: document.getElementById('badgeCountSuccess'),
                badgeFailed: document.getElementById('badgeCountFailed'),
                statusText: document.getElementById('currentStatus'),
                summaryText: document.getElementById('importSummaryText'),
                successBody: document.getElementById('successTableBody'),
                failedBody: document.getElementById('failedTableBody'),
                connectionStatus: document.getElementById('connectionStatus'),
            };

            this.registerEvents();
            this.initEcho();

            // Fallback Polling if Echo not active
            setTimeout(() => {
                if (!this.echoConnected) {
                    this.els.connectionStatus.textContent = 'POLLING...';
                    this.startPolling();
                }
            }, 3000);

            // Initial render
            this.updateCounterBadges();
            if (this.successData.length > 0) this.renderSuccessTable();
            if (this.failedData.length > 0) this.renderFailedTable();

            // Initial State check
            @if(($initialData['is_finished'] ?? false))
                this.handleCompletion(@json($initialData['cached']['success'] ?? 0), @json($initialData['cached']['failed'] ?? 0));
            @elseif(($initialData['progress'] ?? 0) > 0)
                this.showProgressState();
                this.updateUI(@json($initialData['progress'] ?? 0), @json($initialData['cached']['success'] ?? 0), @json($initialData['cached']['failed'] ?? 0), false);
            @endif
        },

        registerEvents() {
            if (this.els.cancelBtn) {
                this.els.cancelBtn.addEventListener('click', () => this.confirmCancel());
            }
        },

        initEcho() {
            if (window.Echo) {
                console.log('[Import] Initializing Echo listener for channel:', `import.students.${this.userId}`);
                window.Echo.private(`import.students.${this.userId}`)
                    .listen('.ImportProgress', (e) => {
                        this.echoConnected = true;
                        this.els.connectionStatus.textContent = 'LIVE STREAMING';

                        if (e.import_id !== this.importId) return;

                        console.log('[Import] Real-time event received:', e);

                        if (e.errors && e.errors.length > 0) {
                            this.failedData.push(...e.errors);
                            this.renderFailedTable();
                        }

                        if (e.imported && e.imported.length > 0) {
                            this.successData.push(...e.imported);
                            this.renderSuccessTable();
                        }

                        this.updateUI(e.percentage, e.processed, e.skipped, e.is_done);

                        if (e.is_done) {
                            this.handleCompletion(e.processed, e.skipped);
                        }
                    });

                this.echoConnected = true;
            }
        },

        startPolling() {
            if (this.pollInterval) return;
            this.pollInterval = setInterval(() => {
                fetch(`/dashboard/students/import/${this.batchId}/status?import_id=${this.importId}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.success && data.data) {
                            const d = data.data;
                            const successCount = d.cached ? d.cached.success : d.processed_jobs;
                            const failedCount = d.cached ? d.cached.failed : d.failed_jobs;

                            this.updateUI(d.progress || 0, successCount, failedCount, d.is_finished || false);

                            if (d.is_finished) {
                                // On finish, fetch full results for accuracy
                                this.loadFullResults();
                                clearInterval(this.pollInterval);
                            }
                        }
                    })
                    .catch(err => console.error('[Import] Poll failed:', err));
            }, 3000);
        },

        showProgressState() {
            if (this.els.loading.style.display !== 'none') {
                this.els.loading.style.display = 'none';
                this.els.progress.style.display = 'block';
            }
        },

        updateUI(percentage, success, failed, isDone) {
            this.showProgressState();

            this.els.progressBar.style.width = percentage + '%';
            this.els.percentage.textContent = Math.round(percentage) + '%';
            this.els.countSuccess.textContent = success;
            this.els.countFailed.textContent = failed;

            this.updateCounterBadges();

            this.els.statusText.innerHTML = isDone
                ? 'FINISHING UP...'
                : `SYNCING DATA... (${Math.round(percentage)}%)`;
        },

        updateCounterBadges() {
            this.els.badgeSuccess.textContent = this.successData.length;
            this.els.badgeFailed.textContent = this.failedData.length;
        },

        renderSuccessTable() {
            const empty = this.els.successBody.querySelector('.empty-row');
            if (empty) empty.remove();

            const displayData = this.successData.slice(-100).reverse();

            this.els.successBody.innerHTML = displayData.map((s, i) => `
                <tr class="border-4 fade-in border-start border-success">
                    <td class="ps-4 text-muted small fw-black">${this.successData.length - i}</td>
                    <td>
                        <div class="fw-bold text-dark">${s.name || '-'}</div>
                        <div class="small text-muted fw-medium">Sinkronisasi Berhasil</div>
                    </td>
                    <td><code class="text-primary fw-bold font-monospace">${s.nisn || '-'}</code></td>
                    <td><span class="border badge text-dark fw-bold">${s.gender === 'Laki-laki' ? 'L' : 'P'}</span></td>
                    <td><span class="fw-black text-dark">${s.rombel || '-'}</span></td>
                    <td class="text-center pe-4">
                        <div class="mx-auto avatar-text avatar-xs bg-soft-success text-success rounded-circle">
                            <i class="feather-check"></i>
                        </div>
                    </td>
                </tr>
            `).join('');
        },

        renderFailedTable() {
            const empty = this.els.failedBody.querySelector('.empty-row');
            if (empty) empty.remove();

            const displayData = this.failedData.slice().reverse();

            this.els.failedBody.innerHTML = displayData.map((f, i) => `
                <tr class="border-4 fade-in border-start border-danger">
                    <td class="ps-4 text-muted small fw-black">${this.failedData.length - i}</td>
                    <td>
                        <div class="fw-bold text-danger">${f.name || 'Unknown'}</div>
                        <div class="small text-muted fw-medium">NISN: ${f.nisn || '-'}</div>
                    </td>
                    <td>
                        <div class="p-2 border rounded shadow-sm bg-soft-danger text-danger small border-danger border-opacity-10 fw-medium">
                            ${f.error || 'Kesalahan sistem'}
                        </div>
                    </td>
                    <td class="text-center pe-4">
                        <button class="btn btn-xs btn-outline-danger rounded-pill fw-bold" onclick="Swal.fire({title: 'Detail Kegagalan', text: '${f.error?.replace(/'/g, "\\'")}', icon: 'error'})">
                            Detail
                        </button>
                    </td>
                </tr>
            `).join('');
        },

        handleCompletion(success, failed) {
            setTimeout(() => {
                this.els.progress.style.display = 'none';
                this.els.finish.style.display = 'block';

                this.els.badgeStatus.innerHTML = `
                    <div class="px-4 py-2 border shadow-sm d-flex align-items-center bg-soft-success text-success rounded-pill fw-bold border-success border-opacity-10">
                        <i class="feather-check-circle me-2 fs-5"></i> Sinkronisasi Selesai
                    </div>
                `;

                this.els.summaryText.innerHTML =
                    `Berhasil menyinkronkan <strong>${success}</strong> data siswa.` +
                    (failed > 0 ? `<div class="mt-3 text-danger fw-bold small"><i class="feather-alert-octagon me-2"></i>Terdapat ${failed} data yang gagal. Silakan periksa log.</div>` : '');

                this.triggerCelebration();
                this.loadFullResults();

                if (failed === 0) {
                    setTimeout(() => {
                        Swal.fire({
                            title: 'Bagus Sekali!',
                            text: 'Semua data telah diimpor tanpa kesalahan.',
                            icon: 'success',
                            confirmButtonText: 'Tutup',
                            confirmButtonColor: '#2563eb'
                        });
                    }, 2000);
                }
            }, 800);
        },

        triggerCelebration() {
            const end = Date.now() + (3 * 1000);
            const colors = ['#2563eb', '#10b981'];

            (function frame() {
              confetti({
                particleCount: 2,
                angle: 60,
                spread: 55,
                origin: { x: 0 },
                colors: colors
              });
              confetti({
                particleCount: 2,
                angle: 120,
                spread: 55,
                origin: { x: 1 },
                colors: colors
              });

              if (Date.now() < end) {
                requestAnimationFrame(frame);
              }
            }());
        },

        loadFullResults() {
            if (!this.importId) return;
            fetch('/dashboard/students/import/data?importId=' + this.importId)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        this.successData = data.data?.students || [];
                        this.failedData = data.data?.errors || [];
                        this.els.countSuccess.textContent = this.successData.length;
                        this.els.countFailed.textContent = this.failedData.length;
                        this.renderSuccessTable();
                        this.renderFailedTable();
                        this.updateCounterBadges();
                    }
                });
        },

        confirmCancel() {
            Swal.fire({
                title: 'Hentikan Sesi?',
                text: "Proses sinkronisasi akan diputus. Data yang sudah masuk tidak akan dihapus.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'YA, HENTIKAN PAKSA',
                cancelButtonText: 'TETAP LANJUTKAN'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`/dashboard/students/import/${this.batchId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': @json(csrf_token()),
                            'Content-Type': 'application/json',
                        }
                    }).then(() => {
                        window.location.href = @json(route('dashboard.students.index'));
                    });
                }
            });
        }
    };

    document.addEventListener('DOMContentLoaded', () => ImportManager.init());
</script>
@endpush
