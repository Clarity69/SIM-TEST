<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MataKuliahRequest extends FormRequest
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
            'kode' => ['required', 'string', 'max:20', Rule::unique('mata_kuliahs', 'kode')->ignore($this->route('mata_kuliah'))],
            'sks' => ['required', 'integer', 'between:1,12'],
            'semester' => ['required', 'integer', 'between:1,14'],
        ];
    }
}
