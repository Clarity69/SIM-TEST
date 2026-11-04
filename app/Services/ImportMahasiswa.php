<?php

namespace App\Services;

use App\Imports\BarisDenganHeader;
use App\Models\Mahasiswa;
use App\Models\Prodi;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use RuntimeException;
use Throwable;

/**
 * Impor mahasiswa dari Excel/CSV: baca -> normalisasi -> validasi per baris -> simpan massal.
 * Semua hasil berupa array skalar agar bisa disimpan di cache antar langkah wizard.
 */
class ImportMahasiswa
{
    public const MAKS_BARIS = 2000;

    /**
     * @return list<array<string, mixed>> baris data mentah (sheet pertama), kunci = header ter-slug
     */
    public function baca(UploadedFile $file): array
    {
        $sheets = Excel::toArray(new BarisDenganHeader, $file);
        $rows = array_values(array_filter($sheets[0] ?? [], fn ($row) => is_array($row) && array_filter($row, fn ($v) => $v !== null && $v !== '')));

        if (! array_key_exists('nim', $rows[0] ?? []) || ! array_key_exists('nama', $rows[0] ?? [])) {
            throw new RuntimeException('Header tidak dikenali. Pastikan baris pertama berisi kolom nim, nama, jenis_kelamin, prodi, ... sesuai template.');
        }

        if (count($rows) > self::MAKS_BARIS) {
            throw new RuntimeException('File berisi '.count($rows).' baris; maksimal '.self::MAKS_BARIS.' baris per impor.');
        }

        return $rows;
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @return array{valid: list<array{baris: int, data: array<string, mixed>, prodi: string}>, invalid: list<array{baris: int, data: array<string, mixed>, errors: list<string>}>}
     */
    public function validasi(array $rows): array
    {
        $prodiMap = $this->petaProdi();
        $nims = array_values(array_filter(array_map(fn ($row) => $this->teks($row['nim'] ?? null), $rows)));
        $sudahAda = Mahasiswa::query()->whereIn('nim', $nims)->pluck('nim')->flip();
        $dilihat = [];
        $valid = [];
        $invalid = [];

        foreach ($rows as $i => $row) {
            $baris = $i + 2; // baris 1 = header
            [$data, $prodiLabel, $errors] = $this->normalisasi($row, $prodiMap);

            $validator = Validator::make($data, [
                'nim' => ['required', 'string', 'max:30'],
                'nama' => ['required', 'string', 'max:255'],
                'jenis_kelamin' => ['required', Rule::in(Mahasiswa::JENIS_KELAMIN)],
                'tanggal_lahir' => ['nullable', 'date', 'before:today'],
                'prodi_id' => ['required', 'integer'],
                'angkatan' => ['nullable', 'integer', 'between:2000,'.(now()->year + 1)],
                'status' => ['required', Rule::in(array_keys(Mahasiswa::STATUS))],
                'telepon' => ['nullable', 'string', 'max:20'],
                'email' => ['nullable', 'email', 'max:255'],
                'alamat' => ['nullable', 'string'],
            ], [
                'prodi_id.required' => 'Program studi tidak dikenali.',
                'jenis_kelamin.in' => 'Jenis kelamin harus Laki-laki/Perempuan (L/P).',
            ], [
                'nim' => 'NIM', 'jenis_kelamin' => 'jenis kelamin', 'tanggal_lahir' => 'tanggal lahir',
            ]);

            $errors = array_merge($errors, $validator->errors()->all());

            if ($data['nim'] !== null && isset($sudahAda[$data['nim']])) {
                $errors[] = 'NIM sudah digunakan.';
            } elseif ($data['nim'] !== null && isset($dilihat[$data['nim']])) {
                $errors[] = "NIM ganda di file (sama dengan baris {$dilihat[$data['nim']]}).";
            }

            if ($data['nim'] !== null) {
                $dilihat[$data['nim']] ??= $baris;
            }

            $tampil = array_merge($data, ['prodi' => $prodiLabel]);

            if ($errors) {
                $invalid[] = ['baris' => $baris, 'data' => $tampil, 'errors' => array_values(array_unique($errors))];
            } else {
                $valid[] = ['baris' => $baris, 'data' => $data, 'prodi' => $prodiLabel];
            }
        }

        return ['valid' => $valid, 'invalid' => $invalid];
    }

    /**
     * Simpan baris valid secara massal. NIM dicek ulang (bisa saja ditambahkan orang lain
     * di antara pratinjau dan konfirmasi).
     *
     * @param  list<array{baris: int, data: array<string, mixed>}>  $valid
     * @return array{berhasil: int, dilewati: int, contoh: list<string>}
     */
    public function simpan(array $valid): array
    {
        $sudahAda = Mahasiswa::query()->whereIn('nim', array_column(array_column($valid, 'data'), 'nim'))->pluck('nim')->flip();
        $sekarang = now();
        $rows = [];

        foreach ($valid as $item) {
            if (! isset($sudahAda[$item['data']['nim']])) {
                $rows[] = $item['data'] + ['created_at' => $sekarang, 'updated_at' => $sekarang];
            }
        }

        DB::transaction(function () use ($rows) {
            foreach (array_chunk($rows, 200) as $chunk) {
                Mahasiswa::insert($chunk);
            }
        });

        // insert() melewati model event; bersihkan cache statistik & daftar secara manual.
        DashboardStats::forget();
        Mahasiswa::bumpVersiDaftar();

        return [
            'berhasil' => count($rows),
            'dilewati' => count($valid) - count($rows),
            'contoh' => array_map(fn ($row) => $row['nama'].' ('.$row['nim'].')', array_slice($rows, 0, 5)),
        ];
    }

    /**
     * @param  array<string, mixed>  $row
     * @param  array<string, array{id: int, label: string}>  $prodiMap
     * @return array{0: array<string, mixed>, 1: string, 2: list<string>}
     */
    private function normalisasi(array $row, array $prodiMap): array
    {
        $errors = [];
        $nim = $this->teks($row['nim'] ?? null);

        $jk = strtolower((string) $this->teks($row['jenis_kelamin'] ?? null));
        $jenisKelamin = match (true) {
            in_array($jk, ['l', 'laki-laki', 'laki laki', 'lk', 'pria'], true) => 'Laki-laki',
            in_array($jk, ['p', 'perempuan', 'pr', 'wanita'], true) => 'Perempuan',
            default => $jk === '' ? null : $jk,
        };

        $tanggal = null;
        $mentahTanggal = $row['tanggal_lahir'] ?? null;
        if ($mentahTanggal !== null && $mentahTanggal !== '') {
            try {
                $tanggal = is_numeric($mentahTanggal)
                    ? Carbon::instance(ExcelDate::excelToDateTimeObject((float) $mentahTanggal))->format('Y-m-d')
                    : Carbon::parse((string) $mentahTanggal)->format('Y-m-d');
            } catch (Throwable) {
                $errors[] = 'Tanggal lahir tidak dikenali.';
            }
        }

        $kunciProdi = strtolower((string) $this->teks($row['prodi'] ?? null));
        $prodi = $prodiMap[$kunciProdi] ?? null;

        $angkatan = $this->teks($row['angkatan'] ?? null);
        if ($angkatan === null && $nim !== null && preg_match('/^(20\d{2})/', $nim, $m)) {
            $angkatan = $m[1];
        }

        $status = $this->teks($row['status'] ?? null);
        $status = $status === null ? 'Aktif' : ucfirst(strtolower($status));

        $telepon = $this->teks($row['telepon'] ?? null);
        if ($telepon !== null && preg_match('/^8\d{8,11}$/', $telepon)) {
            $telepon = '0'.$telepon; // Excel menghapus angka 0 di depan nomor HP
        }

        return [[
            'nim' => $nim,
            'nama' => $this->teks($row['nama'] ?? null),
            'jenis_kelamin' => $jenisKelamin,
            'tanggal_lahir' => $tanggal,
            'prodi_id' => $prodi['id'] ?? null,
            'angkatan' => $angkatan === null ? null : (int) $angkatan,
            'status' => $status,
            'telepon' => $telepon,
            'email' => $this->teks($row['email'] ?? null),
            'alamat' => $this->teks($row['alamat'] ?? null),
        ], $prodi['label'] ?? (string) $this->teks($row['prodi'] ?? null), $errors];
    }

    /**
     * Nilai sel sebagai teks rapi; angka bulat dari Excel (202512001.0) tanpa desimal.
     */
    private function teks(mixed $nilai): ?string
    {
        if ($nilai === null) {
            return null;
        }

        if (is_float($nilai) && floor($nilai) === $nilai) {
            $nilai = number_format($nilai, 0, '', '');
        }

        $nilai = trim((string) $nilai);

        return $nilai === '' ? null : $nilai;
    }

    /**
     * @return array<string, array{id: int, label: string}> singkatan/kode/nama (huruf kecil) => prodi
     */
    private function petaProdi(): array
    {
        $peta = [];

        foreach (Prodi::opsi() as $prodi) {
            $item = ['id' => $prodi->id, 'label' => $prodi->nama_prodi];
            foreach ([$prodi->singkatan, $prodi->kode_prodi, $prodi->nama_prodi] as $kunci) {
                if ($kunci) {
                    $peta[strtolower($kunci)] = $item;
                }
            }
        }

        return $peta;
    }
}
