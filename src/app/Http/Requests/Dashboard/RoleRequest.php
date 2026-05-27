<?php

namespace App\Http\Requests\Dashboard;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('role')?->id ?? $this->route('role');

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('roles')->where('guard_name', $this->input('guard_name'))->ignore($id)],
            'guard_name' => ['required', 'string', 'max:255'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::exists('permissions', 'id')->where('guard_name', $this->input('guard_name'))],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama role wajib diisi.',
            'name.unique' => 'Nama role sudah ada untuk guard ini.',
            'guard_name.required' => 'Guard name wajib diisi.',
            'permissions.*.exists' => 'Permission yang dipilih tidak valid untuk guard ini.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'Nama Role',
            'guard_name' => 'Guard Name',
            'permissions' => 'Hak Akses',
        ];
    }
}
