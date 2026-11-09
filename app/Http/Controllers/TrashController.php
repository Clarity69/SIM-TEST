<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Mahasiswa;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Sampah (khusus admin): mahasiswa yang di-soft-delete bisa dipulihkan atau dihapus permanen.
 */
class TrashController extends Controller
{
    public function index(): View
    {
        $mahasiswas = Mahasiswa::onlyTrashed()
            ->select('id', 'nim', 'nama', 'jenis_kelamin', 'prodi_id', 'foto', 'deleted_at', 'deleted_by')
            ->with(['prodi:id,nama_prodi', 'penghapus:id,name'])
            ->latest('deleted_at')
            ->paginate(15);

        return view('trash.index', compact('mahasiswas'));
    }

    public function restore(Mahasiswa $mahasiswa): RedirectResponse
    {
        abort_unless($mahasiswa->trashed(), 404);

        try {
            $this->pulihkan($mahasiswa);
        } catch (UniqueConstraintViolationException) {
            return back()->with('error', "NIM {$mahasiswa->nim} sudah dipakai mahasiswa aktif; ubah salah satunya sebelum memulihkan.");
        }

        return back()->with('success', "{$mahasiswa->nama} dipulihkan ke Data Mahasiswa.");
    }

    public function destroy(Mahasiswa $mahasiswa): RedirectResponse
    {
        abort_unless($mahasiswa->trashed(), 404);

        $mahasiswa->forceDelete();

        return back()->with('success', "{$mahasiswa->nama} dihapus permanen.");
    }

    public function bulk(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'aksi' => ['required', 'in:pulihkan,hapus'],
            'ids' => ['required', 'array', 'min:1', 'max:500'],
            'ids.*' => ['integer'],
        ]);

        $mahasiswas = Mahasiswa::onlyTrashed()->whereKey($validated['ids'])->get();
        $gagal = 0;

        ActivityLog::tanpaCatat(function () use ($mahasiswas, $validated, &$gagal) {
            foreach ($mahasiswas as $mahasiswa) {
                if ($validated['aksi'] === 'hapus') {
                    $mahasiswa->forceDelete();

                    continue;
                }

                try {
                    $this->pulihkan($mahasiswa);
                } catch (UniqueConstraintViolationException) {
                    $gagal++;
                }
            }
        });

        $berhasil = $mahasiswas->count() - $gagal;

        if ($berhasil > 0) {
            ActivityLog::catat('bulk', ($validated['aksi'] === 'hapus' ? 'Menghapus permanen ' : 'Memulihkan ')."{$berhasil} mahasiswa dari Sampah", properties: [
                'contoh' => $mahasiswas->take(5)->map(fn ($m) => "{$m->nama} ({$m->nim})")->all(),
            ]);
        }

        $pesan = $validated['aksi'] === 'hapus'
            ? "{$berhasil} data dihapus permanen."
            : "{$berhasil} data dipulihkan.".($gagal ? " {$gagal} gagal karena NIM sudah dipakai mahasiswa aktif." : '');

        return back()->with($gagal ? 'error' : 'success', $pesan);
    }

    public function empty(): RedirectResponse
    {
        $jumlah = 0;

        ActivityLog::tanpaCatat(fn () => Mahasiswa::onlyTrashed()->select('id', 'foto')->chunkById(200, function ($mahasiswas) use (&$jumlah) {
            foreach ($mahasiswas as $mahasiswa) {
                $mahasiswa->forceDelete();
                $jumlah++;
            }
        }));

        if ($jumlah > 0) {
            ActivityLog::catat('bulk', "Mengosongkan Sampah ({$jumlah} data dihapus permanen)");
        }

        return back()->with('success', "Sampah dikosongkan ({$jumlah} data dihapus permanen).");
    }

    /**
     * Pulihkan dalam transaksi agar deleted_by ikut dikosongkan secara atomik.
     */
    private function pulihkan(Mahasiswa $mahasiswa): void
    {
        DB::transaction(function () use ($mahasiswa) {
            $mahasiswa->deleted_by = null;
            $mahasiswa->restore();
        });
    }
}
