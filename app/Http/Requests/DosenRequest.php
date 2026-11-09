<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DosenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /** @return array<string, array<mixed>> */
    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:255'],
            'prodi_id' => ['required', 'integer', 'exists:prodis,id'],
            'aktif' => ['required', 'boolean'],
            'nidn' => ['nullable', 'regex:/^(?:[0-9]{10}|[0-9]{16})$/', Rule::unique('dosens', 'nidn')->ignore($this->route('dosen'))],
            'email' => ['nullable', 'email', 'max:255'],
        ];
    }
}
