<?php

namespace App\Http\Requests\Dashboard;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('permission')?->id ?? $this->route('permission');

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('permissions')->where('guard_name', $this->input('guard_name'))->ignore($id)],
            'guard_name' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama permission wajib diisi.',
            'name.unique' => 'Nama permission sudah ada untuk guard ini.',
            'guard_name.required' => 'Guard name wajib diisi.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'Nama Permission',
            'guard_name' => 'Guard Name',
        ];
    }
}
