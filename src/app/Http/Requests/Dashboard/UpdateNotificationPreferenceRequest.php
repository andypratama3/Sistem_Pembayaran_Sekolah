<?php

namespace App\Http\Requests\Dashboard;

use Illuminate\Foundation\Http\FormRequest;

class UpdateNotificationPreferenceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'preferences' => ['required', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'preferences.required' => 'Preferensi notifikasi wajib diisi.',
            'preferences.array' => 'Preferensi notifikasi harus berupa daftar.',
        ];
    }
}
