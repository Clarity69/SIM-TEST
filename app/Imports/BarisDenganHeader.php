<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\Import;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

/**
 * Pembaca file impor: baris pertama sebagai header (di-slug, mis. "Jenis Kelamin"
 * menjadi "jenis_kelamin"), baris kosong dilewati. Dipakai lewat Excel::toArray().
 */
class BarisDenganHeader implements Import, SkipsEmptyRows, WithHeadingRow {}
