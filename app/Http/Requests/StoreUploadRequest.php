<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUploadRequest extends FormRequest
{
    /**
     * Hanya user yang sudah login.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // not_regex CRLF: nama file dipakai di header Content-Disposition
            // saat download, sehingga karakter kontrol harus ditolak (anti header injection).
            'original_name' => ['required', 'string', 'max:255', 'not_regex:/[\r\n\t]/'],
            'file_size' => ['required', 'integer', 'min:1'],
            'mime_type' => ['nullable', 'string', 'max:127'],
            'checksum' => ['nullable', 'string', 'regex:/^[a-fA-F0-9]{64}$/'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'original_name.required' => 'Nama file wajib diisi.',
            'original_name.max' => 'Nama file maksimal 255 karakter.',
            'original_name.not_regex' => 'Nama file tidak boleh mengandung karakter kontrol.',
            'file_size.required' => 'Ukuran file wajib diisi.',
            'file_size.integer' => 'Ukuran file harus berupa angka bulat.',
            'file_size.min' => 'Ukuran file harus lebih dari 0.',
            'checksum.regex' => 'Checksum harus berupa SHA-256 (64 karakter hex).',
        ];
    }
}
