<?php

namespace App\Http\Requests\Dashboard;

use Illuminate\Foundation\Http\FormRequest;

class UpdateClassroomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'classroom_type' => ['required', 'string', 'max:100'],
            'teacher_ids' => ['nullable', 'array'],
            'teacher_ids.*' => ['exists:teachers,id'],
            'subject_ids' => ['nullable', 'array'],
            'subject_ids.*' => ['exists:subjects,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama kelas wajib diisi.',
            'name.max' => 'Nama kelas maksimal 255 karakter.',
            'academic_year_id.required' => 'Tahun akademik wajib dipilih.',
            'academic_year_id.exists' => 'Tahun akademik tidak ditemukan.',
            'classroom_type.required' => 'Tipe kelas wajib diisi.',
            'classroom_type.max' => 'Tipe kelas maksimal 100 karakter.',
            'teacher_ids.*.exists' => 'Guru yang dipilih tidak valid.',
            'subject_ids.*.exists' => 'Mata pelajaran yang dipilih tidak valid.',
        ];
    }
}
