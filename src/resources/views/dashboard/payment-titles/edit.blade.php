@extends('layouts.app')

@section('title', 'Edit Judul Pembayaran')

@section('page-header')
    <x-page-header title="Edit Jenis Pembayaran">
        <x-slot:left>
            {{ Breadcrumbs::render('dashboard.payment-titles.edit', $paymentTitle) }}
        </x-slot:left>
    </x-page-header>

@endsection

@section('content')
    <div class="col-lg-6">
        <div class="card stretch stretch-full">
            <div class="card-header">
                <h5 class="card-title">Form Judul Pembayaran</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('dashboard.payment-titles.update', $paymentTitle) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label class="form-label">Nama Pembayaran <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                            value="{{ old('name', $paymentTitle->name) }}" placeholder="Contoh: SPP Bulanan" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Kode Pembayaran <span class="text-danger">*</span></label>
                        <input type="text" name="code" class="form-control @error('code') is-invalid @enderror"
                            value="{{ old('code', $paymentTitle->code) }}" placeholder="Contoh: SPP" required>
                        @error('code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="hstack gap-2 justify-content-end">
                        <a href="{{ route('dashboard.payment-titles.index') }}" class="btn btn-light">Batal</a>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
