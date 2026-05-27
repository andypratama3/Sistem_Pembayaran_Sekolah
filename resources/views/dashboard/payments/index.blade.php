@extends('layouts.app')

@section('title', 'Manajemen Pembayaran')

@section('page-header')
    <x-page-header create-route="dashboard.payments.create">
        <x-slot:left>
            {{ Breadcrumbs::render('dashboard.payments.index') }}
        </x-slot:left>
        <x-slot:actions>
            <a href="{{ route('dashboard.payments.index') }}?export=true" class="btn btn-success">
                <i class="feather-download me-1"></i> Export
            </a>
        </x-slot:actions>
    </x-page-header>
@endsection

@section('content')
    {{-- Stats Cards --}}
    <div class="mb-4 row g-3">
        <div class="col-md-3">
            <x-card class="text-white border-0 shadow-sm bg-primary h-100">
                <div class="py-3 card-body">
                    <div class="gap-3 mb-2 d-flex align-items-center">
                        <div class="flex-shrink-0 bg-white rounded avatar-text avatar-lg text-primary">
                            <i class="feather-dollar-sign fs-4"></i>
                        </div>
                    </div>
                    <div class="text-white fw-bold" style="font-size: 1.1rem; line-height: 1.4;">Rp {{ number_format($stats['total'], 0, ',', '.') }}</div>
                    <div class="text-white small op-80 text-uppercase fw-bold">Total Tagihan</div>
                </div>
            </x-card>
        </div>
        <div class="col-md-3">
            <x-card class="text-white border-0 shadow-sm bg-success h-100">
                <div class="py-3 card-body">
                    <div class="gap-3 mb-2 d-flex align-items-center">
                        <div class="flex-shrink-0 bg-white rounded avatar-text avatar-lg text-success">
                            <i class="feather-check-circle fs-4"></i>
                        </div>
                    </div>
                    <div class="text-white fw-bold" style="font-size: 1.1rem; line-height: 1.4;">Rp {{ number_format($stats['paid'], 0, ',', '.') }}</div>
                    <div class="text-white small op-80 text-uppercase fw-bold">Sudah Bayar</div>
                </div>
            </x-card>
        </div>
        <div class="col-md-3">
            <x-card class="text-white border-0 shadow-sm bg-warning h-100">
                <div class="py-3 card-body">
                    <div class="gap-3 mb-2 d-flex align-items-center">
                        <div class="flex-shrink-0 bg-white rounded avatar-text avatar-lg text-warning">
                            <i class="feather-clock fs-4"></i>
                        </div>
                    </div>
                    <div class="text-white fw-bold" style="font-size: 1.1rem; line-height: 1.4;">Rp {{ number_format($stats['pending'], 0, ',', '.') }}</div>
                    <div class="text-white small op-80 text-uppercase fw-bold">Pending</div>
                </div>
            </x-card>
        </div>
        <div class="col-md-3">
            <x-card class="text-white border-0 shadow-sm bg-danger h-100">
                <div class="py-3 card-body">
                    <div class="gap-3 mb-2 d-flex align-items-center">
                        <div class="flex-shrink-0 bg-white rounded avatar-text avatar-lg text-danger">
                            <i class="feather-x-circle fs-4"></i>
                        </div>
                    </div>
                    <div class="text-white fw-bold" style="font-size: 1.1rem; line-height: 1.4;">Rp {{ number_format($stats['failed'], 0, ',', '.') }}</div>
                    <div class="text-white small op-80 text-uppercase fw-bold">Gagal/Expired</div>
                </div>
            </x-card>
        </div>
    </div>

    <div class="col-lg-12">
        <x-card full-height class="border-0 shadow-sm">
            @include('components.swal-flash')

            <div id="filterForm" data-table-target="#paymentsTable" class="p-4 m-0 border-bottom-0">
                <form class="row align-items-end g-3">
                    <div class="col-md-4">
                        <label class="mb-1 form-label fs-11 fw-bold text-uppercase text-muted">Status Pembayaran</label>
                        <select name="status" class="border-0 form-select" data-select2-selector="status">
                            <option value="">Semua Status</option>
                            <option value="paid" data-bg="bg-success">Lunas</option>
                            <option value="pending" data-bg="bg-warning">Pending</option>
                            <option value="failed" data-bg="bg-danger">Gagal</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="mb-1 form-label fs-11 fw-bold text-uppercase text-muted">Filter Kelas</label>
                        <select name="classroom_id" class="border-0 form-select" data-select2-selector="default">
                            <option value="">Semua Kelas</option>
                            @foreach ($classrooms as $class)
                                <option value="{{ $class->id }}">{{ $class->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="mb-1 form-label fs-11 fw-bold text-uppercase text-muted">Tahun Tagihan</label>
                        <select name="year" class="border-0 form-select" data-select2-selector="default">
                            <option value="">Pilih Tahun</option>
                            @foreach (range(date('Y'), date('Y') - 3) as $y)
                                <option value="{{ $y }}">{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="button" id="resetFilter" class="btn btn-danger w-100 fw-bold">
                            <i class="feather-refresh-cw me-2"></i> Reset
                        </button>
                    </div>
                </form>
            </div>

            <x-data-table id="paymentsTable" title="Daftar Pembayaran" :columns="[
                ['data' => 'student_name', 'title' => 'Siswa'],
                ['data' => 'classroom', 'title' => 'Kelas'],
                ['data' => 'payment_title', 'title' => 'Jenis Tagihan'],
                ['data' => 'amount', 'title' => 'Jumlah'],
                ['data' => 'status', 'title' => 'Status'],
                ['data' => 'created_at', 'title' => 'Tgl Tagihan'],
            ]" action-route="payments" model="Payment"
                :can-create="auth()->user()->can('create', App\Models\Payment::class)" :can-edit="auth()->user()->can('update', App\Models\Payment::class)" :can-delete="auth()->user()->can('delete', App\Models\Payment::class)" :bulk-actions="['delete' => 'Hapus', 'export' => 'Export']"
                search-placeholder="Cari histori pembayaran..." />
        </x-card>
    </div>

@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Additional custom logic if needed
        });
    </script>
@endpush
