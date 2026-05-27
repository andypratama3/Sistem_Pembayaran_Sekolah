<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportProgressRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'batchId' => ['required', 'string'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
