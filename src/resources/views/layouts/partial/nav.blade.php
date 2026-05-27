<div class="navbar-wrapper">
    <div class="m-header">
        <a href="{{ route('dashboard.index') }}" class="b-brand">
            <img src="{{ asset('assets/images/logo-full.png') }}" alt="" class="logo logo-lg" />
            <img src="{{ asset('assets/images/logo-abbr.png') }}" alt="" class="logo logo-sm" />
        </a>
    </div>
    <div class="navbar-content">
        <ul class="nxl-navbar">
            <li class="nxl-item nxl-caption">
                <label>Navigasi Utama</label>
            </li>

            {{-- Dashboard --}}
            <li class="nxl-item">
                <a href="{{ route('dashboard.index') }}" class="nxl-link">
                    <span class="nxl-micon"><i class="bi bi-speedometer2"></i></span>
                    <span class="nxl-mtext">Dashboard</span>
                </a>
            </li>

            {{-- AKADEMIK --}}
            <li class="nxl-item nxl-caption"><label>Akademik</label></li>

            {{-- Data Master (grouped) --}}
            <li class="nxl-item nxl-hasmenu">
                <a href="javascript:void(0);" class="nxl-link">
                    <span class="nxl-micon"><i class="fa fa-database"></i></span>
                    <span class="nxl-mtext">Data Master</span>
                    <span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
                </a>
                <ul class="nxl-submenu">
                    @can('viewAny', App\Models\Student::class)
                        <li class="nxl-item">
                            <a class="nxl-link" href="{{ route('dashboard.students.index') }}">Siswa</a>
                        </li>
                    @endcan
                    @can('viewAny', App\Models\Classroom::class)
                        <li class="nxl-item">
                            <a class="nxl-link" href="{{ route('dashboard.classrooms.index') }}">Kelas</a>
                        </li>
                    @endcan
                    <li class="nxl-item">
                        <a class="nxl-link" href="{{ route('dashboard.academic-years.index') }}">Tahun Ajaran</a>
                    </li>
                </ul>
            </li>

            {{-- KEUANGAN --}}
            <li class="nxl-item nxl-caption"><label>Keuangan</label></li>

            {{-- Pembayaran (grouped) --}}
            <li class="nxl-item nxl-hasmenu">
                <a href="javascript:void(0);" class="nxl-link">
                    <span class="nxl-micon"><i class="bi bi-cash-stack"></i></span>
                    <span class="nxl-mtext">Pembayaran</span>
                    <span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
                </a>
                <ul class="nxl-submenu">
                    <li class="nxl-item">
                        <a class="nxl-link" href="{{ route('dashboard.payments.index') }}">Daftar Pembayaran</a>
                    </li>
                    <li class="nxl-item">
                        <a class="nxl-link" href="{{ route('dashboard.payment-titles.index') }}">Jenis Pembayaran</a>
                    </li>
                </ul>
            </li>

            {{-- KOMUNIKASI --}}
            <li class="nxl-item nxl-caption"><label>Komunikasi</label></li>

            <li class="nxl-item">
                <a href="{{ route('dashboard.whatsapp-chat.index') }}" class="nxl-link">
                    <span class="nxl-micon"><i class="bi bi-whatsapp"></i></span>
                    <span class="nxl-mtext">WhatsApp Chat</span>
                </a>
            </li>

            <li class="nxl-item">
                <a href="{{ route('dashboard.notifications.list') }}" class="nxl-link">
                    <span class="nxl-micon"><i class="bi bi-bell"></i></span>
                    <span class="nxl-mtext">Notifikasi</span>
                </a>
            </li>

            {{-- SISTEM --}}
            @hasanyrole(['superadmin', 'admin'])
                <li class="nxl-item nxl-caption"><label>Sistem</label></li>

                <li class="nxl-item nxl-hasmenu">
                    <a href="javascript:void(0);" class="nxl-link">
                        <span class="nxl-micon"><i class="bi bi-shield-lock"></i></span>
                        <span class="nxl-mtext">Pengguna & Akses</span>
                        <span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
                    </a>
                    <ul class="nxl-submenu">
                        <li class="nxl-item">
                            <a class="nxl-link" href="{{ route('dashboard.settings.users.index') }}">Pengguna</a>
                        </li>
                        <li class="nxl-item">
                            <a class="nxl-link" href="{{ route('dashboard.settings.roles.index') }}">Role & Izin</a>
                        </li>
                        <li class="nxl-item">
                            <a class="nxl-link" href="{{ route('dashboard.settings.permissions.index') }}">Hak Akses</a>
                        </li>
                    </ul>
                </li>

                <li class="nxl-item nxl-hasmenu">
                    <a href="javascript:void(0);" class="nxl-link">
                        <span class="nxl-micon"><i class="bi bi-gear"></i></span>
                        <span class="nxl-mtext">Pengaturan Sistem</span>
                        <span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
                    </a>
                    <ul class="nxl-submenu">
                        <li class="nxl-item">
                            <a class="nxl-link" href="{{ route('dashboard.settings.notification-preferences.edit') }}">Preferensi Notifikasi</a>
                        </li>
                        <li class="nxl-item">
                            <a class="nxl-link" href="{{ route('dashboard.audit_log.index') }}">Audit Log</a>
                        </li>
                        <li class="nxl-item">
                            <a class="nxl-link" href="#"
                                onclick="event.preventDefault(); document.getElementById('flush-cache-form').submit();">
                                Hapus Cache
                            </a>
                            <form id="flush-cache-form" action="{{ route('dashboard.cache.flush') }}" method="POST" class="d-none">
                                @csrf
                            </form>
                        </li>
                    </ul>
                </li>
            @endhasanyrole
        </ul>
    </div>
</div>
