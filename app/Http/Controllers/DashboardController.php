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

        $persen = [
            'total' => $this->badge(DashboardStats::perubahan($stats['total'], $stats['sebelum']['total'])),
            'laki' => $this->badge(DashboardStats::perubahan($stats['laki'], $stats['sebelum']['laki'])),
            'perempuan' => $this->badge(DashboardStats::perubahan($stats['perempuan'], $stats['sebelum']['perempuan'])),
            'prodi' => $this->badge(DashboardStats::perubahan($stats['totalProdi'], $stats['sebelum']['prodi'])),
        ];

        return view('dashboard', [
            'totalMahasiswa' => $stats['total'],
            'totalLakiLaki' => $stats['laki'],
            'totalPerempuan' => $stats['perempuan'],
            'totalProdi' => $stats['totalProdi'],
            'persen' => $persen,
            'mahasiswaTerbaru' => $stats['terbaru'],
            'mahasiswaPerProdi' => $stats['perProdi'],
        ]);
    }

    /**
     * Menyiapkan data badge persentase: teks, warna, dan ikon panah.
     *
     * @return array{nilai: float, teks: string, kelas: string, ikon: string}
     */
    private function badge(float $nilai): array
    {
        if ($nilai > 0) {
            return ['nilai' => $nilai, 'teks' => '+'.$nilai.'%', 'kelas' => 'bg-success bg-opacity-10 text-success', 'ikon' => 'bi-arrow-up-short'];
        }

        if ($nilai < 0) {
            return ['nilai' => $nilai, 'teks' => $nilai.'%', 'kelas' => 'bg-danger bg-opacity-10 text-danger', 'ikon' => 'bi-arrow-down-short'];
        }

        return ['nilai' => $nilai, 'teks' => '0%', 'kelas' => 'bg-secondary bg-opacity-10 text-secondary', 'ikon' => 'bi-dash'];
    }
}
