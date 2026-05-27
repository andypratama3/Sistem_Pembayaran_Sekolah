<?php

namespace App\Http\Requests\Dashboard;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('userRecord')?->id ?? $this->route('userRecord');
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');

        return [
            'employee_id' => ['nullable', 'string', 'exists:employees,id'],
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($id)],
            'password' => $isUpdate
                                ? ['nullable', 'string', 'min:8', 'confirmed']
                                : ['required', 'string', 'min:8', 'confirmed'],
            'is_active' => ['required', 'boolean'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama wajib diisi.',
            'name.max' => 'Nama maksimal 100 karakter.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'email.max' => 'Email maksimal 150 karakter.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'employee_id.exists' => 'Karyawan yang dipilih tidak valid.',
            'is_active.required' => 'Status aktif wajib dipilih.',
            'is_active.boolean' => 'Status aktif tidak valid.',
            'avatar.image' => 'Avatar harus berupa gambar.',
            'avatar.mimes' => 'Format avatar: jpg, jpeg, png, webp.',
            'avatar.max' => 'Ukuran avatar maksimal 2MB.',
        ];
    }

    public function attributes(): array
    {
        return [
            'employee_id' => 'Karyawan',
            'name' => 'Nama',
            'email' => 'Email',
            'password' => 'Password',
            'is_active' => 'Status Aktif',
            'avatar' => 'Avatar',
        ];
    }
}
