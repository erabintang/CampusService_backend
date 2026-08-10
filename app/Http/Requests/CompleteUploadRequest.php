<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CompleteUploadRequest extends FormRequest
{
    /**
     * Hanya pemilik upload atau admin yang boleh memfinalisasi.
     */
    public function authorize(): bool
    {
        $user = $this->user();

        if ($user === null) {
            return false;
        }

        /** @var \App\Models\Upload $upload */
        $upload = $this->route('upload');

        return $user->isAdmin() || $upload->user_id === $user->id;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'checksum' => ['nullable', 'string', 'regex:/^[a-fA-F0-9]{64}$/'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'checksum.regex' => 'Checksum harus berupa SHA-256 (64 karakter hex).',
        ];
    }
}
