<?php

namespace App\Http\Requests\Dashboard;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WhatsAppChatMessageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'content' => ['required_without:media_file', 'string', 'max:4096'],
            'message_type' => [Rule::in('text', 'image', 'document', 'media', 'template')],
            'reply_to_message_id' => ['sometimes', 'uuid', 'exists:whatsapp_messages,id'],
            'force_send_outside_hours' => ['sometimes', 'boolean'],
            'media_file' => ['sometimes', 'file', 'max:52428800'], // 50MB
        ];
    }

    /**
     * Get custom error messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'content.required_without' => 'Konten atau file media wajib diisi.',
            'content.string' => 'Konten harus berupa teks.',
            'content.max' => 'Konten maksimal 4096 karakter.',
            'message_type.in' => 'Tipe pesan harus: text, image, document, media, atau template.',
            'reply_to_message_id.uuid' => 'ID pesan balasan tidak valid.',
            'reply_to_message_id.exists' => 'Pesan balasan tidak ditemukan.',
            'force_send_outside_hours.boolean' => 'Force send harus benar atau salah.',
            'media_file.file' => 'File media harus berupa file.',
            'media_file.max' => 'File media maksimal 50MB.',
        ];
    }
}
