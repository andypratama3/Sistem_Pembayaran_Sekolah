@extends('layouts.app')

@section('title', 'Detail - ' . $paymentTitle->name ?? 'Payment Title')

@section('page-header')
    <x-page-header title="Detail Jenis Pembayaran">
        <x-slot:left>
            {{ Breadcrumbs::render('dashboard.payment-titles.show', $paymentTitle) }}
        </x-slot:left>
    </x-page-header>

@endsection

@section('content')
    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header border-bottom">
                    <h5 class="card-title mb-0">Detail Judul Pembayaran</h5>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <label class="fw-bold text-muted text-uppercase small">Nama Pembayaran:</label>
                        <h5 class="mb-0">{{ $paymentTitle->name }}</h5>
                    </div>

                    <div class="mb-4">
                        <label class="fw-bold text-muted text-uppercase small">Kode Pembayaran:</label>
                        <p class="mb-0">
                            <span class="badge bg-soft-primary text-primary fs-14">{{ $paymentTitle->code }}</span>
                        </p>
                    </div>

                    <div class="row">
                        <div class="col-sm-6">
                            <label class="fw-bold text-muted text-uppercase small">Total Siswa Terkait:</label>
                            <h4 class="mb-0">{{ $paymentTitle->payments_count ?? 0 }}</h4>
                        </div>
                        <div class="col-sm-6">
                            <label class="fw-bold text-muted text-uppercase small">Tanggal Dibuat:</label>
                            <p class="mb-0">{{ $paymentTitle->created_at->format('d M Y') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card">
                <div class="card-header border-bottom">
                    <h5 class="card-title mb-0">Aksi</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-column gap-2">
                        <a href="{{ route('dashboard.payment-titles.index') }}" class="btn btn-outline-secondary">
                            <i class="feather-arrow-left me-2"></i>Kembali
                        </a>
                        @can('update', $paymentTitle)
                            <a href="{{ route('dashboard.payment-titles.edit', $paymentTitle) }}"
                                class="btn btn-outline-primary">
                                <i class="feather-edit-3 me-2"></i>Edit
                            </a>
                        @endcan
                        @can('delete', $paymentTitle)
                            <form method="POST" action="{{ route('dashboard.payment-titles.destroy', $paymentTitle) }}"
                                class="d-block">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger w-100 delete-confirm">
                                    <i class="feather-trash-2 me-2"></i>Hapus
                                </button>
                            </form>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                document.querySelectorAll('.delete-confirm').forEach(btn => {
                    btn.addEventListener('click', function(e) {
                        e.preventDefault();
                        if (confirm(
                                'Apakah Anda yakin ingin menghapus judul pembayaran ini? Ini akan mempengaruhi semua pembayaran terkait.'
                                )) {
                            this.closest('form').submit();
                        }
                    });
                });
            });
        </script>
    @endpush
@endsection
