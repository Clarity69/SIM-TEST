<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validasi bersama untuk store dan update program studi.
 */
class ProdiRequest extends FormRequest
{
    /**
     * Semua route prodi sudah berada di balik middleware auth.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $prodi = $this->route('prodi');

        return [
            'kode_prodi' => [
                'required',
                'string',
                'max:20',
                Rule::unique('prodis', 'kode_prodi')->ignore($prodi?->id),
            ],
            'singkatan' => ['nullable', 'string', 'max:10'],
            'nama_prodi' => ['required', 'string', 'max:255'],
            'fakultas' => ['nullable', 'string', 'max:255'],
            'kaprodi' => ['nullable', 'string', 'max:255'],
            'kaprodi_jabatan' => ['nullable', 'string', 'max:100'],
            'kaprodi_nidn' => ['nullable', 'string', 'max:20'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'kode_prodi' => 'kode prodi',
            'nama_prodi' => 'nama program studi',
            'kaprodi' => 'nama kaprodi',
            'kaprodi_jabatan' => 'jabatan',
            'kaprodi_nidn' => 'NIDN',
        ];
    }

    /**
     * Jabatan kosong dikembalikan ke default kolom (Ketua Program Studi).
     */
    protected function prepareForValidation(): void
    {
        if (blank($this->input('kaprodi_jabatan'))) {
            $this->merge(['kaprodi_jabatan' => 'Ketua Program Studi']);
        }
    }
}
