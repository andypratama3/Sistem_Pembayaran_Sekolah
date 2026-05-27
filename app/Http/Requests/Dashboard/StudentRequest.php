<?php

namespace App\Http\Requests\Dashboard;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StudentRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if (! $this->filled('gender') && $this->filled('sex')) {
            $this->merge([
                'gender' => $this->input('sex'),
            ]);
        }
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('studentRecord')?->id;
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');

        return [
            'user_id' => ['nullable', 'exists:users,id'],
            'name' => ['required', 'string', 'max:255'],
            'gender' => ['required', 'in:Laki-laki,Perempuan'],
            'birth_place' => ['required', 'string', 'max:255'],
            'birth_date' => ['required', 'date', 'before:today'],
            'nisn' => [
                'required',
                'string',
                'regex:/^\d{10}$/',
                Rule::unique('students', 'nisn')->ignore($id),
            ],
            'religion' => ['required', 'string', 'max:50'],
            'spp' => ['nullable', 'integer', 'min:0'],
            'dpp' => ['nullable', 'integer', 'min:0'],
            'uniform_fee' => ['nullable', 'integer', 'min:0'],
            'va_number' => ['nullable', 'string'],
            'previous_school_name' => ['nullable', 'string', 'max:255'],
            'previous_school_address' => ['nullable', 'string', 'max:255'],
            'entry_year' => ['nullable', 'string', 'max:10'],
            'entry_date' => ['nullable', 'date'],
            'scholarship' => ['nullable', 'string', 'max:255'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'guardian_type' => ['required', 'in:orang_tua,wali'],
            'father_name' => ['nullable', 'string', 'max:255'],
            'mother_name' => ['nullable', 'string', 'max:255'],
            'father_education' => ['nullable', 'string', 'max:255'],
            'mother_education' => ['nullable', 'string', 'max:255'],
            'father_occupation' => ['nullable', 'string', 'max:255'],
            'mother_occupation' => ['nullable', 'string', 'max:255'],
            'guardian_name' => [
                'nullable',
                'string',
                'max:255',
                Rule::requiredIf(fn () => $this->guardian_type === 'wali'),
            ],
            'guardian_occupation' => ['nullable', 'string', 'max:255'],
            'guardian_address' => ['nullable', 'string', 'max:255'],
            'rt' => ['nullable', 'string', 'max:10'],
            'rw' => ['nullable', 'string', 'max:10'],
            'province_id' => ['nullable', 'string'],
            'regency_id' => ['nullable', 'string'],
            'district_id' => ['nullable', 'string'],
            'village_id' => ['nullable', 'string'],
            'street' => ['nullable', 'string'],
            'residence_type' => ['nullable', 'string', 'max:50'],
            'phone' => ['required', 'string', 'max:20'],
            'classroom_id' => ['nullable', 'exists:classrooms,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama wajib diisi.',
            'nisn.required' => 'NISN wajib diisi.',
            'nisn.regex' => 'NISN harus terdiri dari 10 angka.',
            'nisn.unique' => 'NISN sudah terdaftar.',
            'birth_date.before' => 'Tanggal lahir harus sebelum hari ini.',
            'gender.required' => 'Jenis kelamin wajib dipilih.',
            'gender.in' => 'Jenis kelamin tidak valid.',
            'guardian_name.required' => 'Nama wali wajib diisi jika tipe penjaga adalah wali.',
            'phone.required' => 'Nomor HP wajib diisi.',
            'photo.image' => 'File harus berupa gambar.',
            'photo.max' => 'Ukuran gambar maksimal 2MB.',
        ];
    }

    // Helper methods for file handling
    public function getPhotoPath(): ?string
    {
        if ($this->hasFile('photo')) {
            return 'students/photos';
        }

        return null;
    }

    public function shouldDeleteOldPhoto(): bool
    {
        return $this->isMethod('PUT') || $this->isMethod('PATCH');
    }
}
