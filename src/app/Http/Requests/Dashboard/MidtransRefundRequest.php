<?php

namespace App\Http\Requests\Dashboard;

use Illuminate\Foundation\Http\FormRequest;

class MidtransRefundRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->can('refund payments') || auth()->user()->hasRole(['admin', 'finance']);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'amount' => ['nullable', 'numeric', 'min:1'],
        ];
    }

    /**
     * Get custom error messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'amount.numeric' => 'Jumlah pengembalian harus berupa angka.',
            'amount.min' => 'Jumlah pengembalian harus minimal 1.',
        ];
    }
}
