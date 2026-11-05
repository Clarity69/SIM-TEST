<?php

namespace App\Http\Controllers;

use App\Services\DashboardStats;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Menampilkan statistik ringkasan dan data terbaru di Dashboard.
     * Angka dihitung oleh DashboardStats (query agregat + cache 5 menit).
     */
    public function index(DashboardStats $dashboardStats): View
    {
        $stats = $dashboardStats->get();

        return view('dashboard', [
            'totalMahasiswa' => $stats['total'],
            'totalLakiLaki' => $stats['laki'],
            'totalPerempuan' => $stats['perempuan'],
            'totalProdi' => $stats['totalProdi'],
            'tren' => [
                'total' => DashboardStats::perubahan($stats['total'], $stats['sebelum']['total']),
                'laki' => DashboardStats::perubahan($stats['laki'], $stats['sebelum']['laki']),
                'perempuan' => DashboardStats::perubahan($stats['perempuan'], $stats['sebelum']['perempuan']),
                'prodi' => DashboardStats::perubahan($stats['totalProdi'], $stats['sebelum']['prodi']),
            ],
            'selisih' => [
                'total' => $stats['total'] - $stats['sebelum']['total'],
                'laki' => $stats['laki'] - $stats['sebelum']['laki'],
                'perempuan' => $stats['perempuan'] - $stats['sebelum']['perempuan'],
                'prodi' => $stats['totalProdi'] - $stats['sebelum']['prodi'],
            ],
            'terakhir' => $stats['terakhir'],
            'mahasiswaTerbaru' => collect($stats['terbaru']),
            'mahasiswaPerProdi' => collect($stats['perProdi']),
            'perAngkatan' => $stats['perAngkatan'],
        ]);
    }
}
