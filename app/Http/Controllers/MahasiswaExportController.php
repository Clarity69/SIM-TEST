<?php

namespace App\Http\Controllers;

use App\Exports\MahasiswaExport;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Ekspor data mahasiswa ke Excel/CSV mengikuti pencarian & filter aktif.
 */
class MahasiswaExportController extends Controller
{
    public function __invoke(Request $request): BinaryFileResponse
    {
        $csv = $request->query('format') === 'csv';
        $filters = $request->only(['search', 'prodi_id', 'jenis_kelamin', 'status', 'angkatan', 'sort', 'direction']);
        ActivityLog::catat('export', 'Mengekspor data mahasiswa ke '.($csv ? 'CSV' : 'Excel'), properties: ['filter' => array_filter($filters)]);

        return Excel::download(
            new MahasiswaExport($filters),
            'data-mahasiswa-'.now()->format('Ymd-His').($csv ? '.csv' : '.xlsx'),
            $csv ? ExcelFormat::CSV : ExcelFormat::XLSX,
        );
    }
}
