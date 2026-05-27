@extends('layouts.app')

@section('title', 'Detail Tagihan #' . $payment->order_id)

@section('page-header')
    <x-page-header title="Detail Pembayaran">
        <x-slot:left>
            {{ Breadcrumbs::render('dashboard.payments.show', $payment) }}
        </x-slot:left>
    </x-page-header>

@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card stretch stretch-full">
                @include('components.swal-flash')
                <div class="p-0 card-body">
                    <div class="p-4 invoice-container p-md-5">
                        <!-- Invoice Header -->
                        <div class="mb-5 d-md-flex align-items-start justify-content-between">
                            <div>
                                <h2 class="mb-2 fw-bold text-primary">INVOICE</h2>
                                <h6 class="mb-0 text-muted">Order ID: <span class="text-dark">#{{ $payment->order_id }}</span>
                                </h6>
                            </div>
                            <div class="mt-3 text-md-end mt-md-0">
                                @php
                                    $badges = [
                                        'paid' => 'success',
                                        'completed' => 'success',
                                        'pending' => 'warning',
                                        'failed' => 'danger',
                                        'cancelled' => 'danger',
                                        'expired' => 'secondary',
                                    ];
                                    $badge = $badges[$payment->status] ?? 'info';
                                @endphp
                                <span
                                    class="badge bg-{{ $badge }}-subtle text-{{ $badge }} fs-14 px-3 py-2 mb-2">
                                    {{ strtoupper($payment->status) }}
                                </span>
                                <p class="mb-0 text-muted small">Tanggal Tagihan:
                                    {{ $payment->created_at->format('d M Y, H:i') }}</p>
                            </div>
                        </div>

                        <!-- Invoice Info -->
                        <div class="mb-5 row g-4">
                            <div class="col-sm-6 col-lg-4">
                                <h6 class="mb-3 fw-bold text-muted text-uppercase small">Ditagihkan Kepada:</h6>
                                <h5 class="mb-1 fw-bold">{{ $payment->student->name ?? 'Student Name' }}</h5>
                                <p class="mb-1 text-muted">{{ $payment->student->nisn ?? '-' }}</p>
                                <p class="mb-0 text-muted">{{ $payment->classroom->name ?? 'Classroom' }}</p>
                            </div>
                            <div class="col-sm-6 col-lg-4">
                                <h6 class="mb-3 fw-bold text-muted text-uppercase small">Informasi Pembayaran:</h6>
                                <p class="mb-1 text-muted"><span class="fw-bold text-dark">Tipe:</span>
                                    {{ $payment->paymentTitle->name ?? '-' }}</p>
                                <p class="mb-1 text-muted"><span class="fw-bold text-dark">Metode:</span>
                                    {{ strtoupper($payment->payment_type ?? 'ONLINE') }}</p>
                                <p class="mb-0 text-muted"><span class="fw-bold text-dark">ID Transaksi:</span>
                                    {{ $payment->transaction_id ?? '-' }}</p>
                            </div>
                            <div class="col-lg-4 text-lg-end">
                                <h6 class="mb-3 fw-bold text-muted text-uppercase small">Total Tagihan:</h6>
                                <h2 class="mb-0 fw-bold text-dark">Rp
                                    {{ number_format($payment->gross_amount, 0, ',', '.') }}</h2>
                            </div>
                        </div>

                        <!-- Invoice Table -->
                        <div class="mb-5 table-responsive">
                            <table class="table table-borderless border-bottom">
                                <thead>
                                    <tr>
                                        <th class="py-3 ps-3">Deskripsi Tagihan</th>
                                        <th class="py-3">Periode</th>
                                        <th class="py-3 text-end pe-3">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="py-4 ps-3">
                                            <h6 class="mb-1 fw-bold">
                                                {{ $payment->paymentTitle->name ?? 'Tagihan Sekolah' }}</h6>
                                            <p class="mb-0 text-muted small">Pembayaran untuk keperluan operasional sekolah.
                                            </p>
                                        </td>
                                        <td class="py-4">
                                            @if ($payment->start_date && $payment->end_date)
                                                {{ $payment->start_date->format('d/m/Y') }} -
                                                {{ $payment->end_date->format('d/m/Y') }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="py-4 text-end pe-3 fw-bold">Rp
                                            {{ number_format($payment->gross_amount, 0, ',', '.') }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Summary -->
                        <div class="row justify-content-end">
                            <div class="col-md-5 col-lg-4">
                                <div class="mb-3 d-flex justify-content-between">
                                    <span class="text-muted fw-bold text-uppercase small">Total Tagihan</span>
                                    <h4 class="mb-0 fw-bold text-dark">Rp
                                        {{ number_format($payment->gross_amount, 0, ',', '.') }}</h4>
                                </div>
                                <div class="mb-4 dropdown-divider"></div>

                                <div class="gap-2 hstack justify-content-end no-print">
                                    @if ($payment->status === 'pending')
                                        <button type="button" class="btn btn-primary w-100" id="payButton">
                                            <i class="feather-credit-card me-2"></i> Bayar Sekarang
                                        </button>

                                        @can('mark-paid', $payment)
                                            <form action="{{ route('dashboard.payments.mark-paid', $payment->id) }}"
                                                method="POST" class="w-100">
                                                @csrf
                                                <button type="submit" class="btn btn-success w-100">
                                                    <i class="feather-check-circle me-2"></i> Mark Paid
                                                </button>
                                            </form>
                                        @endcan
                                    @endif

                                    <button onclick="window.print()" class="btn btn-light w-100">
                                        <i class="feather-printer me-2"></i> Cetak
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Payment Information -->
    <div class="mt-4 row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Detail Pembayaran</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3 row">
                        <div class="col-sm-5 text-muted">Metode Pembayaran:</div>
                        <div class="col-sm-7 fw-bold">{{ strtoupper($payment->payment_type ?? 'ONLINE') }}</div>
                    </div>
                    <div class="mb-3 row">
                        <div class="col-sm-5 text-muted">ID Transaksi:</div>
                        <div class="col-sm-7 fw-bold">{{ $payment->transaction_id ?? '-' }}</div>
                    </div>
                    <div class="mb-3 row">
                        <div class="col-sm-5 text-muted">VA Number:</div>
                        <div class="col-sm-7 fw-bold">{{ $payment->va_number ?? '-' }}</div>
                    </div>
                    <div class="mb-3 row">
                        <div class="col-sm-5 text-muted">Tanggal Buat:</div>
                        <div class="col-sm-7 fw-bold">{{ $payment->created_at->format('d M Y H:i') }}</div>
                    </div>
                    <div class="row">
                        <div class="col-sm-5 text-muted">Terakhir Update:</div>
                        <div class="col-sm-7 fw-bold">{{ $payment->updated_at->format('d M Y H:i') }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Riwayat Pembayaran Siswa</h5>
                </div>
                <div class="card-body">
                    @php
                        $studentPayments = $payment->student
                            ->payments()
                            ->where('status', 'paid')
                            ->orderBy('created_at', 'desc')
                            ->take(5)
                            ->get();
                    @endphp
                    @if ($studentPayments->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm table-borderless">
                                <thead>
                                    <tr>
                                        <th>Tipe</th>
                                        <th>Jumlah</th>
                                        <th>Tanggal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($studentPayments as $p)
                                        <tr>
                                            <td>{{ $p->paymentTitle->name ?? '-' }}</td>
                                            <td class="fw-bold">Rp {{ number_format($p->gross_amount, 0, ',', '.') }}</td>
                                            <td>{{ $p->created_at->format('d M Y') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="py-4 text-center text-muted">Belum ada riwayat pembayaran</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @if ($payment->status === 'pending')
        <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}">
        </script>
        <script>
            document.getElementById('payButton').onclick = function() {
                // Trigger payment from service or directly use token if available
                @if ($payment->snap_token)
                    snap.pay('{{ $payment->snap_token }}', {
                        onSuccess: function(result) {
                            window.location.reload();
                        },
                        onPending: function(result) {
                            window.location.reload();
                        },
                        onError: function(result) {
                            console.error(result);
                        }
                    });
                @else
                    // Fallback: If no token, maybe trigger AJAX to get one
                    fetch('{{ route('dashboard.midtrans.snap-token', $payment->id) }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                snap.pay(data.data.snap_token, {
                                    onSuccess: function(result) {
                                        window.location.reload();
                                    },
                                    onPending: function(result) {
                                        window.location.reload();
                                    },
                                    onError: function(result) {
                                        console.error(result);
                                    }
                                });
                            } else {
                                alert('Gagal mendapatkan token pembayaran.');
                            }
                        });
                @endif
            };
        </script>
    @endif

    <style>
        @media print {

            .no-print,
            .nxl-navigation,
            .nxl-header,
            .page-header,
            .nxl-footer {
                display: none !important;
            }

            .nxl-container {
                margin: 0 !important;
                padding: 0 !important;
            }

            .card {
                border: none !important;
                box-shadow: none !important;
            }
        }
    </style>
@endpush
