<div class="header-wrapper">
    <!--! [Start] Header Left !-->
    <div class="gap-4 header-left d-flex align-items-center">
        <a href="javascript:void(0);" class="nxl-head-mobile-toggler" id="mobile-collapse">
            <div class="hamburger hamburger--arrowturn">
                <div class="hamburger-box">
                    <div class="hamburger-inner"></div>
                </div>
            </div>
        </a>
        <div class="nxl-navigation-toggle">
            <a href="javascript:void(0);" id="menu-mini-button">
                <i class="feather-align-left"></i>
            </a>
            <a href="javascript:void(0);" id="menu-expend-button" style="display: none">
                <i class="feather-arrow-right"></i>
            </a>
        </div>
        <div class="nxl-drp-link nxl-lavel-mega-menu">
            <div class="nxl-lavel-mega-menu-toggle d-flex d-lg-none">
                <a href="javascript:void(0)" id="nxl-lavel-mega-menu-hide">
                    <i class="feather-arrow-left me-2"></i>
                    <span>Kembali</span>
                </a>
            </div>
        </div>
    </div>
    <!--! [End] Header Left !-->

    <!--! [Start] Header Right !-->
    <div class="header-right ms-auto">
        <div class="d-flex align-items-center">
            {{-- Language Dropdown Removed as requested --}}

            {{-- Fullscreen --}}
            <div class="nxl-h-item d-none d-sm-flex">
                <div class="full-screen-switcher">
                    <a href="javascript:void(0);" class="nxl-head-link me-0" id="global-fullscreen-toggle"
                        onclick="toggleSimpleFullscreen(event)">
                        <i class="feather-maximize maximize"></i>
                        <i class="feather-minimize minimize"></i>
                    </a>
                </div>
            </div>

            {{-- Search Modal Trigger --}}
            <div class="nxl-h-item">
                <a href="javascript:void(0);" class="nxl-head-link me-0" data-bs-toggle="modal"
                    data-bs-target="#searchModal">
                    <i class="feather-search"></i>
                </a>
            </div>

            {{-- Dark / Light --}}
            <div class="nxl-h-item dark-light-theme">
                <a href="javascript:void(0);" class="nxl-head-link me-0 dark-button"><i class="feather-moon"></i></a>
                <a href="javascript:void(0);" class="nxl-head-link me-0 light-button" style="display:none"><i
                        class="feather-sun"></i></a>
            </div>

            {{-- Timesheets --}}
            <div class="dropdown nxl-h-item">
                <a href="javascript:void(0);" class="nxl-head-link me-0" data-bs-toggle="dropdown" role="button"
                    data-bs-auto-close="outside">
                    <i class="feather-clock"></i>
                    @if ($activeTimer)
                        <span class="badge bg-danger nxl-h-badge pulse-ring">1</span>
                    @endif
                </a>
                <div class="dropdown-menu dropdown-menu-end nxl-h-dropdown nxl-timesheets-menu">
                    <div class="d-flex justify-content-between align-items-center timesheets-head">
                        <h6 class="mb-0 fw-bold text-dark">Log Kerja</h6>
                        <a href="{{ route('dashboard.timesheets.index') }}" class="fs-11 text-success text-end ms-auto">
                            <span>Lihat Semua</span>
                        </a>
                    </div>
                    <div class="p-0 timesheets-body">
                        @if ($activeTimer)
                            <div class="p-3 border-bottom bg-soft-primary">
                                <div class="mb-2 d-flex align-items-center justify-content-between">
                                    <span class="badge bg-primary rounded-pill">Berjalan</span>
                                    <span class="fw-black text-primary" id="headerTimerDisplay"
                                        data-start="{{ $activeTimer->start_time->toIso8601String() }}">
                                        {{ $activeTimer->getFormattedDuration() }}
                                    </span>
                                </div>
                                <h6 class="mb-1 fw-bold text-dark text-truncate">{{ $activeTimer->task->title }}</h6>
                                <p class="mb-3 small text-muted">{{ strtoupper($activeTimer->task->category) }}</p>
                                <button type="button" class="btn btn-sm btn-danger w-100 stop-timer-btn"
                                    data-id="{{ $activeTimer->id }}">
                                    <i class="feather-stop-circle me-1"></i> Berhenti
                                </button>
                            </div>
                        @else
                            <div class="p-4 text-center">
                                <i class="mb-3 feather-clock fs-1 text-muted"></i>
                                <p class="mb-0 text-muted small">Tidak ada timer aktif</p>
                            </div>
                        @endif

                        <div class="p-3 recent-logs">
                            <h6 class="mb-3 fs-11 fw-black text-uppercase ls-1 text-muted">Log Terbaru</h6>
                            <div class="gap-3 vstack">
                                @forelse($recentTimesheets as $log)
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="overflow-hidden">
                                            <div class="fw-bold text-dark text-truncate small">{{ $log->task->title }}
                                            </div>
                                            <div class="text-muted extra-small">
                                                {{ $log->start_time->format('d M, H:i') }}</div>
                                        </div>
                                        <span
                                            class="badge bg-light text-dark fw-bold">{{ $log->getFormattedDuration() }}</span>
                                    </div>
                                @empty
                                    <div class="py-2 text-center">
                                        <span class="text-muted small">Belum ada log</span>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                    <div class="text-center timesheets-footer">
                        <a href="{{ route('dashboard.timesheets.index') }}" class="fs-13 fw-semibold text-dark">Kelola
                            Timesheet</a>
                    </div>
                </div>
            </div>

            {{-- Notifications --}}
            <div class="dropdown nxl-h-item">
                <a class="nxl-head-link me-3" data-bs-toggle="dropdown" href="#" role="button"
                    data-bs-auto-close="outside">
                    <i class="feather-bell"></i>
                    <span class="badge bg-danger nxl-h-badge" id="notif-badge"
                        @if ($unreadTotal == 0) style="display: none" @endif>
                        {{ $unreadTotal > 99 ? '99+' : $unreadTotal }}
                    </span>
                </a>
                <div class="dropdown-menu dropdown-menu-end nxl-h-dropdown nxl-notifications-menu">
                    <div class="d-flex justify-content-between align-items-center notifications-head">
                        <h6 class="mb-0 fw-bold text-dark">Notifikasi</h6>
                        <a href="javascript:void(0);" id="btn-mark-all-read"
                            class="fs-11 text-success text-end ms-auto" data-bs-toggle="tooltip"
                            title="Tandai Dibaca">
                            <i class="feather-check"></i><span> Tandai Dibaca</span>
                        </a>
                    </div>
                    <div id="notif-list">
                        @forelse($initialNotifications as $n)
                            <div class="notifications-item">
                                <img src="{{ asset('assets/images/avatar/1.png') }}" class="border rounded me-3" />
                                <div class="notifications-desc">
                                    <a href="{{ $getNotificationUrl($n) }}" class="font-body text-truncate-2-line">
                                        <span class="fw-semibold text-dark">{{ $n->title }}</span>
                                        {{ $n->message }}
                                    </a>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="notifications-date text-muted border-bottom border-bottom-dashed">
                                            {{ $n->created_at->diffForHumans() }}</div>
                                        <div class="gap-2 d-flex align-items-center">
                                            <a href="javascript:void(0)"
                                                class="bg-gray-300 d-block wd-8 ht-8 rounded-circle btn-notif-read"
                                                data-id="{{ $n->id }}" title="Tandai dibaca"></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="p-3 text-center text-muted">Belum ada notifikasi</div>
                        @endforelse
                    </div>
                    <div class="text-center notifications-footer">
                        <a href="{{ route('dashboard.notifications.list') }}"
                            class="fs-13 fw-semibold text-dark">Semua Notifikasi</a>
                    </div>
                </div>
            </div>

            {{-- User Dropdown --}}
            <div class="dropdown nxl-h-item">
                <a href="javascript:void(0);" data-bs-toggle="dropdown" role="button" data-bs-auto-close="outside">
                    <img src="{{ asset('assets/images/avatar/1.png') }}" alt="user-image"
                        class="img-fluid user-avtar me-0" />
                </a>
                <div class="dropdown-menu dropdown-menu-end nxl-h-dropdown nxl-user-dropdown">
                    <div class="dropdown-header">
                        <div class="d-flex align-items-center">
                            <img src="{{ asset('assets/images/avatar/1.png') }}" alt="user-image"
                                class="img-fluid user-avtar" />
                            <div>
                                <h6 class="mb-0 text-dark">
                                    {{ Auth::user()->name ?? 'User' }}
                                    <span
                                        class="badge bg-soft-success text-success ms-1">{{ Auth::user()->roles->first()->name ?? 'N/A' }}</span>
                                </h6>
                                <span class="fs-12 fw-medium text-muted">{{ Auth::user()->email ?? '' }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="dropdown-divider"></div>
                    <a href="{{ route('dashboard.profile.edit') }}" class="dropdown-item">
                        <i class="feather-user"></i><span>Profil Saya</span>
                    </a>
                    <a href="{{ route('dashboard.audit_log.index') }}" class="dropdown-item">
                        <i class="feather-activity"></i><span>Log Aktivitas</span>
                    </a>
                    <a href="{{ route('dashboard.notifications.list') }}" class="dropdown-item">
                        <i class="feather-bell"></i><span>Notifikasi</span>
                    </a>
                    <a href="{{ route('dashboard.settings.notification-preferences.edit') }}" class="dropdown-item">
                        <i class="feather-settings"></i><span>Pengaturan</span>
                    </a>
                    <div class="dropdown-divider"></div>
                    {{-- Logout — trigger modal, bukan langsung submit --}}
                    <a href="javascript:void(0);" class="dropdown-item text-danger" id="btn-logout-trigger">
                        <i class="feather-log-out"></i><span>Keluar</span>
                    </a>
                </div>
            </div>

        </div>
    </div>
    <!--! [End] Header Right !-->
</div>
@push('modals')
    {{-- ── Modal Konfirmasi Logout ─────────────────────────────────────────────── --}}
    <div class="modal fade" id="logoutModal" tabindex="-1" data-bs-keyboard="false" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
            <div class="modal-content">

                <div class="pb-0 border-0 modal-header">
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>

                <div class="px-4 pt-0 pb-3 text-center modal-body">
                    <div class="mx-auto mb-3 avatar-text avatar-xl rounded-circle bg-danger-subtle text-danger"
                        style="width:64px;height:64px;font-size:28px;display:flex;align-items:center;justify-content:center;">
                        <i class="feather-log-out"></i>
                    </div>
                    <h5 class="mb-1 fw-semibold">Keluar dari Akun?</h5>
                    <p class="mb-0 text-muted" style="font-size:13px;">
                        Sesi Anda akan diakhiri. Pastikan semua pekerjaan sudah tersimpan sebelum keluar.
                    </p>
                </div>

                <div class="gap-2 pt-0 pb-4 border-0 modal-footer justify-content-center">
                    <button type="button" class="px-4 btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="px-4 btn btn-danger">
                            <i class="feather-log-out me-1"></i> Ya, Logout
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </div>
    {{-- ── End Modal ─────────────────────────────────────────────────────────── --}}
@endpush

@push('scripts')
    <script>
        // ── Helper API ────────────────────────────────────────────────────────────
        async function postJson(url, data = {}) {
            const res = await fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            });
            if (!res.ok) throw new Error(await res.text());
            return res.json();
        }

        // ── Notifications ─────────────────────────────────────────────────────────
        // Generate URL notifikasi
        function getNotifUrl(n) {
            let url = '#';
            if (n.data && n.data.url) return n.data.url;
            switch (n.type) {
                case 'grade':
                case 'grade_posted':
                    url = n.data?.grade_id ? "{{ route('dashboard.grades.show', ':id') }}".replace(':id', n.data
                        .grade_id) : "{{ route('dashboard.grades.index') }}";
                    break;
                case 'attendance':
                    url = "{{ route('dashboard.attendances.index') }}";
                    break;
                case 'payment':
                case 'payment_completed':
                    url = n.data?.payment_id ? "{{ route('dashboard.payments.show', ':id') }}".replace(':id', n.data
                        .payment_id) : "{{ route('dashboard.payments.index') }}";
                    break;
                case 'leave_approved':
                case 'leave_rejected':
                    url = n.data?.leave_request_id ? "{{ route('dashboard.leave-requests.show', ':id') }}".replace(':id', n
                        .data.leave_request_id) : "{{ route('dashboard.leave-requests.index') }}";
                    break;
                case 'salary_grade':
                    url = "{{ route('dashboard.payroll.salary-grades.index') }}";
                    break;
                case 'education_allowance':
                    url = "{{ route('dashboard.payroll.education-allowances.index') }}";
                    break;
                case 'structural_allowance':
                    url = "{{ route('dashboard.payroll.structural-allowances.index') }}";
                    break;
                case 'functional_allowance':
                    url = "{{ route('dashboard.payroll.functional-allowances.index') }}";
                    break;
                case 'salary_rate':
                    url = "{{ route('dashboard.payroll.salary-rates.index') }}";
                    break;
            }
            return url;
        }

        async function loadNotifications() {
            const list = document.getElementById('notif-list');
            if (!list) return;
            try {
                const res = await fetch("{{ route('dashboard.notifications.index') }}");
                const data = await res.json();
                list.innerHTML = '';
                if (data.length === 0) {
                    list.innerHTML = '<div class="p-3 text-center text-muted">Belum ada notifikasi</div>';
                    return;
                }
                data.forEach(n => {
                    list.innerHTML += `
                <div class="notifications-item">
                    <img src="{{ asset('assets/images/avatar/1.png') }}" class="border rounded me-3" />
                    <div class="notifications-desc">
                        <a href="${getNotifUrl(n)}" class="font-body text-truncate-2-line">
                            <span class="fw-semibold text-dark">${n.title}</span>
                            ${n.message}
                        </a>
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="notifications-date text-muted border-bottom border-bottom-dashed">${moment(n.created_at).fromNow()}</div>
                            <div class="gap-2 d-flex align-items-center">
                                <a href="javascript:void(0)" class="bg-gray-300 d-block wd-8 ht-8 rounded-circle btn-notif-read" data-id="${n.id}" title="Tandai dibaca"></a>
                            </div>
                        </div>
                    </div>
                </div>`;
                });
            } catch (e) {
                console.warn('loadNotifications error:', e);
            }
        }

        async function loadUnreadCount() {
            try {
                const res = await fetch("{{ route('dashboard.notifications.unread-count') }}", {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const parsedCount = Number.parseInt(await res.text(), 10);
                const count = Number.isFinite(parsedCount) ? parsedCount : 0;

                // Update Header Badge
                const badge = document.getElementById('notif-badge');
                if (badge) {
                    badge.innerText = count > 99 ? '99+' : count;
                    badge.style.display = count > 0 ? 'inline-block' : 'none';
                }

                // Update Sidebar Badge (Sync)
                const sidebarBadge = document.getElementById('sidebar-notif-badge');
                if (sidebarBadge) {
                    sidebarBadge.innerText = count > 99 ? '99+' : count;
                    sidebarBadge.style.display = count > 0 ? 'inline-block' : 'none';
                }
            } catch (e) {
                console.warn('loadUnreadCount:', e);
            }
        }

        // Mark single — event delegation
        document.addEventListener('click', async function(e) {
            const btn = e.target.closest('.btn-notif-read');
            if (!btn || btn.classList.contains('bg-success')) return;
            const id = btn.dataset.id;
            if (!id) return;
            try {
                const url = "{{ route('dashboard.notifications.read', ':id') }}".replace(':id', id);
                await postJson(url);
                btn.classList.remove('bg-gray-300');
                btn.classList.add('bg-success');
                btn.title = 'Sudah dibaca';
                loadUnreadCount();
            } catch (e) {
                console.warn('markAsRead:', e.message);
            }
        });

        // Mark all
        document.addEventListener('click', async function(e) {
            if (!e.target.closest('#btn-mark-all-read')) return;
            try {
                await postJson("{{ route('dashboard.notifications.read-all') }}");
                document.querySelectorAll('.btn-notif-read.bg-gray-300').forEach(function(el) {
                    el.classList.remove('bg-gray-300');
                    el.classList.add('bg-success');
                    el.title = 'Sudah dibaca';
                });
                loadUnreadCount();
            } catch (e) {
                console.warn('markAllRead:', e.message);
            }
        });

        // Logout trigger — prefer user's modal if it exists
        document.addEventListener('click', function(e) {
            if (e.target.closest('#btn-logout-trigger')) {
                new bootstrap.Modal(document.getElementById('logoutModal')).show();
            }
        });

        // Initial load sync
        document.addEventListener('DOMContentLoaded', function() {
            // loadUnreadCount(); // Already Blade-rendered, but can refresh if needed
        });
    </script>
@endpush
