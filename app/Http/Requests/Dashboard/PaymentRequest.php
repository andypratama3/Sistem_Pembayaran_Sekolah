<?php

namespace App\Http\Requests\Dashboard;

use Illuminate\Foundation\Http\FormRequest;

class PaymentRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if (! $this->filled('payment_title_id') && is_array($this->input('payment_title_ids')) && ! empty($this->input('payment_title_ids'))) {
            $this->merge([
                'payment_title_id' => $this->input('payment_title_ids')[0],
            ]);
        }
    }

    public function authorize(): bool
    {
        return auth()->user()->can('create-payments') || auth()->user()->hasRole(['admin', 'finance']);
    }

    public function rules(): array
    {
        $id = $this->route('payment')?->id;

        return [
            'student_id' => ['required', 'exists:students,id'],
            'classroom_id' => ['required', 'exists:classrooms,id'],
            'payment_title_id' => ['required', 'exists:payment_titles,id'],
            'gross_amount' => ['required', 'numeric', 'min:1'],
            'status' => ['required', 'in:pending,partial,completed,overdue,cancelled'],
            'payment_method' => ['nullable', 'in:cash,bank_transfer,credit_card,e_wallet'],
            'transaction_id' => ['nullable', 'string', 'max:100'],
            'receipt_number' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'student_id.required' => 'Siswa harus dipilih',
            'student_id.exists' => 'Siswa tidak ditemukan',
            'classroom_id.required' => 'Kelas harus dipilih',
            'classroom_id.exists' => 'Kelas tidak ditemukan',
            'payment_title_id.required' => 'Jenis pembayaran harus dipilih',
            'payment_title_id.exists' => 'Jenis pembayaran tidak ditemukan',
            'gross_amount.required' => 'Jumlah pembayaran harus diisi',
            'gross_amount.numeric' => 'Jumlah pembayaran harus berupa angka',
            'gross_amount.min' => 'Jumlah pembayaran minimal 1',
            'status.required' => 'Status pembayaran harus dipilih',
            'status.in' => 'Status pembayaran tidak valid',
            'payment_method.in' => 'Metode pembayaran tidak valid',
            'transaction_id.max' => 'ID transaksi maksimal 100 karakter',
            'receipt_number.max' => 'Nomor kwitansi maksimal 50 karakter',
        ];
    }

    // Helper method to generate receipt number
    public function generateReceiptNumber(): string
    {
        return 'REC-'.date('Ymd').'-'.str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);
    }

    // Helper method to determine if payment is overdue
    public function isOverdue(): bool
    {
        return $this->input('status') === 'overdue' ||
               ($this->input('status') === 'partial' && now()->diffInDays($this->input('due_date', now())) > 0);
    }

    // Helper method for payment validation
    public function isValidPayment(): bool
    {
        $status = $this->input('status');
        $amount = $this->input('gross_amount');

        return $amount > 0 && in_array($status, ['pending', 'partial', 'completed', 'overdue', 'cancelled']);
    }
}
