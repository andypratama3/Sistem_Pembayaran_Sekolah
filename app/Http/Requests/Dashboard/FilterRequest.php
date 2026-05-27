<?php

namespace App\Http\Requests\Dashboard;

use Illuminate\Foundation\Http\FormRequest;

class FilterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Usually authorized if they can view the index
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        if ($this->has('classroom_id') && ! is_array($this->classroom_id)) {
            // Strip null or empty strings
            if (! empty($this->classroom_id)) {
                $this->merge([
                    'classroom_id' => [$this->classroom_id],
                ]);
            } else {
                $this->merge([
                    'classroom_id' => null,
                ]);
            }
        }
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            // Common filters
            'status' => 'nullable|string|max:50',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',

            // Student/Classroom filters
            'classroom_id' => 'nullable|array',
            'classroom_id.*' => 'exists:classrooms,id',
            'student_id' => 'nullable|exists:students,id',
            'academic_year_id' => 'nullable|exists:academic_years,id',

            // Payment filters
            'payment_title_id' => 'nullable|exists:payment_titles,id',
            'min_amount' => 'nullable|numeric|min:0',
            'max_amount' => 'nullable|numeric|min:0',

            // Employee/HR filters
            'employee_id' => 'nullable|exists:employees,id',
            'type' => 'nullable|string|max:50',
            'category' => 'nullable|string|max:50',
        ];
    }

    /**
     * Get custom error messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'status.string' => 'Status harus berupa teks.',
            'status.max' => 'Status maksimal 50 karakter.',
            'start_date.date' => 'Tanggal mulai harus berupa tanggal yang valid.',
            'end_date.date' => 'Tanggal akhir harus berupa tanggal yang valid.',
            'end_date.after_or_equal' => 'Tanggal akhir harus sama atau setelah tanggal mulai.',
            'classroom_id.array' => 'Kelas harus berupa daftar.',
            'classroom_id.*.exists' => 'Kelas yang dipilih tidak valid.',
            'student_id.exists' => 'Siswa tidak ditemukan.',
            'academic_year_id.exists' => 'Tahun akademik tidak ditemukan.',
            'payment_title_id.exists' => 'Judul pembayaran tidak ditemukan.',
            'min_amount.numeric' => 'Jumlah minimum harus berupa angka.',
            'min_amount.min' => 'Jumlah minimum tidak boleh kurang dari 0.',
            'max_amount.numeric' => 'Jumlah maksimum harus berupa angka.',
            'max_amount.min' => 'Jumlah maksimum tidak boleh kurang dari 0.',
            'employee_id.exists' => 'Karyawan tidak ditemukan.',
            'type.string' => 'Tipe harus berupa teks.',
            'type.max' => 'Tipe maksimal 50 karakter.',
            'category.string' => 'Kategori harus berupa teks.',
            'category.max' => 'Kategori maksimal 50 karakter.',
        ];
    }
}
