@extends('layouts.app')

@section('title', 'Workflow Pendaftaran Calon Siswa')

@section('page-header')
    <x-page-header title="Workflow PPDB">
        <x-slot:left>
            {{ Breadcrumbs::render('dashboard.admissions.workflow') }}
        </x-slot:left>
    </x-page-header>
@endsection

@section('content')
    <div class="col-lg-12">
        <div class="card stretch stretch-full">
            <div class="card-header">
                <h5 class="card-title">Status Alur Pendaftaran</h5>
                <p class="text-muted small mb-0">Kelola status dan keputusan pendaftaran calon siswa</p>
            </div>
            <div class="card-body">
                @include('components.swal-flash')

                <div id="filterForm" class="p-4 border-bottom m-0 mb-4">
                    <div class="row align-items-center g-3">
                        <div class="col-md-3">
                            <label class="form-label fw-bold text-dark text-uppercase small">Filter Status</label>
                            <select name="status_filter" class="form-control" data-select2-selector="default"
                                id="statusFilter">
                                <option value="">Semua Status</option>
                                <option value="pending">Menunggu Review</option>
                                <option value="under_review">Sedang Direview</option>
                                <option value="approved">Diterima</option>
                                <option value="rejected">Ditolak</option>
                                <option value="enrolled">Terdaftar</option>
                                <option value="cancelled">Dibatalkan</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold text-dark text-uppercase small">Cari Pendaftar</label>
                            <input type="text" name="search" class="form-control"
                                placeholder="Nama atau nomor telefon..." id="searchInput">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="button" class="btn btn-light-brand w-100" id="resetFilter">
                                <i class="feather-refresh-cw me-2"></i> Reset
                            </button>
                        </div>
                        <div class="col-md-4 d-flex align-items-end gap-2">
                            @can('export', App\Models\Admission::class)
                                <button type="button" class="btn btn-outline-primary w-100" id="exportBtn">
                                    <i class="feather-download me-2"></i> Export
                                </button>
                            @endcan
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="workflowTable">
                        <thead>
                            <tr>
                                <th>Nama Pendaftar</th>
                                <th>No. Telefon</th>
                                <th>Status Saat Ini</th>
                                <th>Tanggal Submit</th>
                                <th>Status Bayar</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($admissions as $admission)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="avatar-text avatar-sm bg-soft-primary text-primary">
                                                {{ strtoupper(substr($admission->name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <a href="{{ route('dashboard.admissions.show', $admission) }}"
                                                    class="fw-bold text-dark">
                                                    {{ $admission->name }}
                                                </a>
                                                <p class="text-muted small mb-0">ID: {{ $admission->order_id }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $admission->phone }}</td>
                                    <td>
                                        <x-admission-status-badge :status="$admission->status" />
                                    </td>
                                    <td>{{ $admission->submitted_at?->format('d M Y H:i') ?? '-' }}</td>
                                    <td>
                                        @php
                                            $paymentBadgeClass =
                                                $admission->payment_status === 'Lunas' ? 'bg-success' : 'bg-warning';
                                        @endphp
                                        <span
                                            class="badge {{ $paymentBadgeClass }}">{{ $admission->payment_status ?? '-' }}</span>
                                    </td>
                                    <td class="text-end">
                                        <div class="dropdown">
                                            <button type="button" class="btn btn-light btn-sm dropdown-toggle"
                                                data-bs-toggle="dropdown">
                                                <i class="feather-more-vertical"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end">
                                                <a href="{{ route('dashboard.admissions.show', $admission) }}"
                                                    class="dropdown-item">
                                                    <i class="feather-eye me-2"></i> Lihat Detail
                                                </a>
                                                @can('update', $admission)
                                                    <a href="{{ route('dashboard.admissions.edit', $admission) }}"
                                                        class="dropdown-item">
                                                        <i class="feather-edit-3 me-2"></i> Edit
                                                    </a>
                                                @endcan

                                                @if ($admission->status === \App\Models\Admission::STATUS_PENDING)
                                                    @can('review', $admission)
                                                        <button type="button" class="dropdown-item workflow-action"
                                                            data-action="review" data-id="{{ $admission->id }}">
                                                            <i class="feather-eye me-2"></i> Tandai Review
                                                        </button>
                                                    @endcan
                                                    @can('approve', $admission)
                                                        <button type="button" class="dropdown-item workflow-action"
                                                            data-action="approve" data-id="{{ $admission->id }}">
                                                            <i class="feather-check me-2 text-success"></i> Setujui
                                                        </button>
                                                    @endcan
                                                    @can('reject', $admission)
                                                        <button type="button" class="dropdown-item workflow-action"
                                                            data-action="reject" data-id="{{ $admission->id }}">
                                                            <i class="feather-x me-2 text-danger"></i> Tolak
                                                        </button>
                                                    @endcan
                                                @elseif ($admission->status === \App\Models\Admission::STATUS_APPROVED)
                                                    @can('enroll', $admission)
                                                        <a href="{{ route('dashboard.admissions.enroll', $admission) }}"
                                                            class="dropdown-item"
                                                            onclick="return confirm('Yakin daftar sebagai siswa?')">
                                                            <i class="feather-arrow-right me-2 text-primary"></i> Daftarkan
                                                            Siswa
                                                        </a>
                                                    @endcan
                                                @endif

                                                @can('delete', $admission)
                                                    <div class="dropdown-divider"></div>
                                                    <button type="button" class="dropdown-item text-danger delete-admission"
                                                        data-id="{{ $admission->id }}">
                                                        <i class="feather-trash-2 me-2"></i> Hapus
                                                    </button>
                                                @endcan
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        <i class="feather-inbox fs-32 mb-2 d-block"></i>
                                        Tidak ada data pendaftaran
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($admissions->hasPages())
                    <div class="d-flex justify-content-end mt-4">
                        {{ $admissions->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Review Modal --}}
    <div class="modal fade" id="reviewModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="reviewForm" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Tandai Sedang Direview</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="review_notes" class="form-label">Catatan Review</label>
                            <textarea class="form-control" id="review_notes" name="notes" rows="4"
                                placeholder="Masukkan catatan review..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Lanjutkan Review</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Approve Modal --}}
    <div class="modal fade" id="approveModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="approveForm" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Setujui Pendaftaran</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="approve_reason" class="form-label">Alasan Persetujuan</label>
                            <textarea class="form-control" id="approve_reason" name="reason" rows="3"
                                placeholder="Masukkan alasan persetujuan..."></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="approve_classroom" class="form-label">Kelas Tujuan</label>
                            <select class="form-control" id="approve_classroom" name="classroom_id"
                                data-select2-selector="default">
                                <option value="">-- Pilih Kelas (Opsional) --</option>
                                @foreach (\App\Models\Classroom::active()->get() as $classroom)
                                    <option value="{{ $classroom->id }}">{{ $classroom->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success">Setujui</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Reject Modal --}}
    <div class="modal fade" id="rejectModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="rejectForm" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Tolak Pendaftaran</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="reject_reason" class="form-label">Alasan Penolakan <span
                                    class="text-danger">*</span></label>
                            <textarea class="form-control" id="reject_reason" name="decision_reason" rows="4"
                                placeholder="Masukkan alasan penolakan..." required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="reject_notes" class="form-label">Catatan Tambahan</label>
                            <textarea class="form-control" id="reject_notes" name="notes" rows="3"
                                placeholder="Masukkan catatan tambahan (opsional)..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">Tolak</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            let currentAdmissionId = null;

            // Filter functionality
            $('#statusFilter, #searchInput').on('change keyup', function() {
                filterTable();
            });

            $('#resetFilter').on('click', function() {
                $('#statusFilter').val('').trigger('change');
                $('#searchInput').val('');
                filterTable();
            });

            function filterTable() {
                const status = $('#statusFilter').val();
                const search = $('#searchInput').val().toLowerCase();

                $('#workflowTable tbody tr').each(function() {
                    const row = $(this);
                    const rowStatus = row.find('td:eq(2)').text().toLowerCase();
                    const rowSearch = row.find('td:eq(0)').text().toLowerCase() + ' ' +
                        row.find('td:eq(1)').text().toLowerCase();

                    const statusMatch = !status || rowStatus.includes(status);
                    const searchMatch = !search || rowSearch.includes(search);

                    row.toggle(statusMatch && searchMatch);
                });
            }

            // Workflow actions
            $('.workflow-action').on('click', function() {
                currentAdmissionId = $(this).data('id');
                const action = $(this).data('action');

                if (action === 'review') {
                    $('#reviewForm').attr('action', `/dashboard/admissions/${currentAdmissionId}/review`);
                    new bootstrap.Modal(document.getElementById('reviewModal')).show();
                } else if (action === 'approve') {
                    $('#approveForm').attr('action', `/dashboard/admissions/${currentAdmissionId}/approve`);
                    new bootstrap.Modal(document.getElementById('approveModal')).show();
                } else if (action === 'reject') {
                    $('#rejectForm').attr('action', `/dashboard/admissions/${currentAdmissionId}/reject`);
                    new bootstrap.Modal(document.getElementById('rejectModal')).show();
                }
            });

            // Delete functionality
            $('.delete-admission').on('click', function() {
                const admissionId = $(this).data('id');
                window.ConfirmAction(
                    'Hapus Pendaftaran?',
                    'Apakah Anda yakin ingin menghapus data pendaftaran ini?',
                    'Ya, Hapus!'
                ).then((result) => {
                    if (result.value) {
                        window.location.href =
                        `/dashboard/admissions/${admissionId}?_method=DELETE`;
                    }
                });
            });

            // Export functionality
            $('#exportBtn').on('click', function() {
                window.location.href = '{{ route('dashboard.bulk-operations.export') }}?model=Admission';
            });
        });
    </script>
@endpush
