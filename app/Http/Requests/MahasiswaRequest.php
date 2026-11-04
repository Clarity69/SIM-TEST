<?php

namespace App\Http\Requests;

use App\Models\Mahasiswa;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validasi bersama untuk store dan update data mahasiswa.
 */
class MahasiswaRequest extends FormRequest
{
    /**
     * Semua route mahasiswa sudah berada di balik middleware auth.
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
        $mahasiswa = $this->route('mahasiswa');

        return [
            'nim' => [
                'required',
                'string',
                'max:30',
                Rule::unique('mahasiswas', 'nim')->ignore($mahasiswa?->id)->withoutTrashed(),
            ],
            'nama' => ['required', 'string', 'max:255'],
            'jenis_kelamin' => ['required', Rule::in(Mahasiswa::JENIS_KELAMIN)],
            'tanggal_lahir' => ['nullable', 'date', 'before:today'],
            'alamat' => ['nullable', 'string'],
            'telepon' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'prodi_id' => ['required', 'integer', 'exists:prodis,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'nim' => 'NIM',
            'nama' => 'nama',
            'jenis_kelamin' => 'jenis kelamin',
            'tanggal_lahir' => 'tanggal lahir',
            'prodi_id' => 'program studi',
        ];
    }
}
