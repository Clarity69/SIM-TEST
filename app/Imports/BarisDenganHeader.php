<?php

namespace App\Imports;

use App\Services\ImportMahasiswa;
use Maatwebsite\Excel\Concerns\Import;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithLimit;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/**
 * Pembaca file impor: baris pertama sebagai header (di-slug, mis. "Jenis Kelamin"
 * menjadi "jenis_kelamin"), baris kosong dilewati. Dipakai lewat Excel::toArray().
 */
class BarisDenganHeader implements Import, SkipsEmptyRows, WithHeadingRow, WithLimit, WithMultipleSheets
{
    public function __construct(private ?string $sheetName = null) {}

    public function limit(): int
    {
        return ImportMahasiswa::MAKS_BARIS + 1;
    }

    /** @return array<int|string, Import> */
    public function sheets(): array
    {
        return [$this->sheetName ?? 0 => $this];
    }
}
