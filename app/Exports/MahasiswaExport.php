<?php

namespace App\Exports;

use App\Models\Mahasiswa;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Ekspor data mahasiswa (xlsx/csv) sesuai filter aktif atau baris terpilih.
 * Kolom sama dengan template impor sehingga hasil ekspor bisa diimpor ulang.
 *
 * @implements WithMapping<Mahasiswa>
 */
class MahasiswaExport implements FromQuery, ShouldAutoSize, WithColumnFormatting, WithHeadings, WithMapping, WithStyles
{
    public const KOLOM = ['nim', 'nama', 'jenis_kelamin', 'tanggal_lahir', 'prodi', 'angkatan', 'status', 'telepon', 'email', 'alamat'];

    /**
     * @param  array<string, mixed>  $filters
     * @param  list<int>|null  $ids  bila diisi, hanya baris ini (aksi massal)
     */
    public function __construct(
        private array $filters = [],
        private ?array $ids = null,
    ) {}

    public function query(): Builder
    {
        return Mahasiswa::query()
            ->select('id', 'nim', 'nama', 'jenis_kelamin', 'tanggal_lahir', 'prodi_id', 'angkatan', 'status', 'telepon', 'email', 'alamat', 'created_at')
            ->with('prodi:id,kode_prodi,singkatan')
            ->when($this->ids !== null, fn (Builder $q) => $q->whereKey($this->ids))
            ->filter($this->filters);
    }

    /**
     * @return list<string>
     */
    public function headings(): array
    {
        return self::KOLOM;
    }

    /**
     * @param  Mahasiswa  $mahasiswa
     * @return list<mixed>
     */
    public function map($mahasiswa): array
    {
        return [
            $mahasiswa->nim,
            $mahasiswa->nama,
            $mahasiswa->jenis_kelamin,
            $mahasiswa->tanggal_lahir?->format('Y-m-d'),
            $mahasiswa->prodi->singkatan ?: $mahasiswa->prodi->kode_prodi,
            $mahasiswa->angkatan,
            $mahasiswa->status,
            $mahasiswa->telepon,
            $mahasiswa->email,
            $mahasiswa->alamat,
        ];
    }

    /**
     * NIM dan telepon sebagai teks agar nol di depan tidak hilang di Excel.
     *
     * @return array<string, string>
     */
    public function columnFormats(): array
    {
        return ['A' => NumberFormat::FORMAT_TEXT, 'H' => NumberFormat::FORMAT_TEXT];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
