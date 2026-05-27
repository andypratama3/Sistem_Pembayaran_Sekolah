<?php

namespace App\Http\Requests\Dashboard;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePaymentTitleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('payment_titles', 'name')],
            'code' => ['required', 'string', 'max:50', Rule::unique('payment_titles', 'code')],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama judul pembayaran wajib diisi.',
            'name.max' => 'Nama judul pembayaran maksimal 255 karakter.',
            'name.unique' => 'Judul pembayaran sudah terdaftar.',
            'code.required' => 'Kode judul pembayaran wajib diisi.',
            'code.max' => 'Kode judul pembayaran maksimal 50 karakter.',
            'code.unique' => 'Kode judul pembayaran sudah terdaftar.',
        ];
    }
}
