@extends('layouts.app')

@section('title', 'Judul Pembayaran')

@section('page-header')
    <x-page-header create-route="dashboard.payment-titles.create">
        <x-slot:left>
            {{ Breadcrumbs::render('dashboard.payment-titles.index') }}
        </x-slot:left>
    </x-page-header>
@endsection

@section('content')
    <div class="col-lg-12">
        <div class="card stretch stretch-full">
            @include('components.swal-flash')

            <div id="filterForm" data-table-target="#paymentTitlesTable" class="m-3 row g-3">
                <h5>Filter Jenis Tagihan</h5>

                <div class="col-md-8">
                    <label class="form-label small fw-bold text-uppercase">Cari</label>
                    <input type="text" name="search" class="form-control" placeholder="Cari jenis tagihan...">
                </div>
                <div class="justify-center col-md-4 align-items-end d-flex">
                    <button type="button" id="resetFilter" class="btn btn-sm btn-outline-secondary">
                        <i class="m-1 feather-refresh-cw"></i> Reset
                    </button>
                </div>
            </div>

            <x-data-table id="paymentTitlesTable" title="Daftar Jenis Tagihan" :columns="[
                ['data' => 'name', 'title' => 'Nama Pembayaran'],
                ['data' => 'code', 'title' => 'Kode Pembayaran'],
            ]" action-route="payment-titles" model="PaymentTitle"
                :can-create="auth()->user()->can('create', App\Models\PaymentTitle::class)" :can-edit="auth()->user()->can('update', App\Models\PaymentTitle::class)" :can-delete="auth()->user()->can('delete', App\Models\PaymentTitle::class)" :bulk-actions="['delete' => 'Hapus', 'export' => 'Export']"
                search-placeholder="Cari judul pembayaran..." />
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            const table = $('#paymentTitlesTable').DataTable();
            const baseUrl = '{{ route('dashboard.payment-titles.index') }}';
        });
    </script>
@endpush
