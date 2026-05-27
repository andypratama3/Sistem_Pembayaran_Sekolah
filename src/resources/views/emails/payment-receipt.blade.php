@extends('emails.layout')

@section('content')
    <h2>Halo, {{ $payment->student->name }}</h2>
    <p>Terima kasih telah melakukan pembayaran. Kami telah menerima pembayaran Anda dengan rincian sebagai berikut:</p>

    <div class="info-card">
        <div class="row">
            <span class="label">Nomor Order:</span>
            <span>#{{ $payment->order_id }}</span>
        </div>
        <div class="row">
            <span class="label">Tipe Pembayaran:</span>
            <span>{{ $payment->paymentTitle->name }}</span>
        </div>
        <div class="row">
            <span class="label">Jumlah:</span>
            <span>Rp {{ number_format($payment->gross_amount, 0, ',', '.') }}</span>
        </div>
        <div class="row">
            <span class="label">Tanggal:</span>
            <span>{{ $payment->updated_at->format('d M Y H:i') }}</span>
        </div>
        <div class="row">
            <span class="label">Status:</span>
            <span style="color: #10b981; font-weight: 600;">{{ strtoupper($payment->status) }}</span>
        </div>
    </div>

    <p>Pembayaran ini akan otomatis masuk ke dalam rekam jejak keuangan sekolah Anda. Jika ada pertanyaan, silakan hubungi bagian keuangan.</p>

    <div style="text-align: center;">
        <a href="{{ route('dashboard.payments.show', $payment->id) }}" class="button">Lihat Rincian di Dashboard</a>
    </div>

    <p style="margin-top: 40px; border-top: 1px solid #e2e8f0; padding-top: 20px; font-size: 13px; color: #64748b;">
        Email ini dikirimkan secara otomatis dari Sistem Informasi {{ config('app.name') }}. Harap tidak membalas email ini.
    </p>
@endsection
