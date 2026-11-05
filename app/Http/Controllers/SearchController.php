<?php

namespace App\Http\Controllers;

use App\Models\Mahasiswa;
use App\Models\Prodi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * Pencarian global untuk palet perintah (Ctrl+K): mahasiswa (NIM/nama) dan program studi.
 */
class SearchController extends Controller
{
    public const MAKS_MAHASISWA = 6;

    public function __invoke(Request $request): JsonResponse
    {
        $query = mb_substr($request->string('q')->trim()->toString(), 0, 100);

        if (mb_strlen($query) < 2) {
            return response()->json(['results' => []]);
        }

        // Satu query (JOIN prodi) dan hasilnya di-cache per versi data, sama seperti daftar mahasiswa.
        $mahasiswa = Cache::remember('cari.'.Mahasiswa::versiDaftar().'.'.md5(mb_strtolower($query)), 300, fn () => Mahasiswa::query()
            ->join('prodis', 'prodis.id', '=', 'mahasiswas.prodi_id')
            ->select('mahasiswas.id', 'mahasiswas.nim', 'mahasiswas.nama', 'mahasiswas.status', 'prodis.singkatan as prodi_singkatan', 'prodis.nama_prodi as prodi_nama')
            ->filter(['search' => $query, 'sort' => 'nama', 'direction' => 'asc'])
            ->limit(self::MAKS_MAHASISWA)
            ->toBase()
            ->get()
            ->map(fn ($row) => [
                'type' => 'mahasiswa',
                'title' => $row->nama,
                'subtitle' => "{$row->nim} · ".($row->prodi_singkatan ?: $row->prodi_nama)." · {$row->status}",
                'url' => route('mahasiswa.show', $row->id),
            ])
            ->all());

        $kata = mb_strtolower($query);
        $prodi = Prodi::opsi()
            ->filter(fn (Prodi $prodi) => str_contains(mb_strtolower("{$prodi->nama_prodi} {$prodi->singkatan} {$prodi->kode_prodi}"), $kata))
            ->take(3)
            ->map(fn (Prodi $prodi) => [
                'type' => 'prodi',
                'title' => $prodi->nama_prodi,
                'subtitle' => "Program studi · {$prodi->kode_prodi}",
                'url' => route('prodi.show', $prodi->id),
            ])
            ->values()
            ->all();

        return response()->json(['results' => [...$mahasiswa, ...$prodi]]);
    }
}
