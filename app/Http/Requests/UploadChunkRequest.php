<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadChunkRequest extends FormRequest
{
    /**
     * Hanya pemilik upload atau admin yang boleh mengirim chunk.
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
            'index' => ['required', 'integer', 'min:0'],
            'chunk' => ['required', 'file'],
            'chunk_checksum' => ['nullable', 'string', 'regex:/^[a-fA-F0-9]{64}$/'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'index.required' => 'Index chunk wajib diisi.',
            'index.integer' => 'Index chunk harus berupa angka bulat.',
            'index.min' => 'Index chunk tidak boleh negatif.',
            'chunk.required' => 'Data chunk wajib dikirim.',
            'chunk_checksum.regex' => 'Checksum chunk harus berupa SHA-256 (64 karakter hex).',
        ];
    }
}
