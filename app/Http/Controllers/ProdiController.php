<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProdiRequest;
use App\Models\Prodi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProdiController extends Controller
{
    /**
     * Menampilkan daftar program studi beserta jumlah mahasiswa dan fitur pencarian.
     */
    public function index(Request $request)
    {
        $query = Prodi::withCount('mahasiswas');

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $operator = $query->getConnection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
            $pattern = '%'.addcslashes($search, '%_\\').'%';

            $query->where(function ($q) use ($operator, $pattern) {
                $q->where('kode_prodi', $operator, $pattern)
                    ->orWhere('singkatan', $operator, $pattern)
                    ->orWhere('nama_prodi', $operator, $pattern)
                    ->orWhere('fakultas', $operator, $pattern);
            });
        }

        $prodis = $query->orderBy('nama_prodi', 'asc')
            ->paginate(10)
            ->withQueryString();

        return view('prodi.index', compact('prodis'));
    }

    /**
     * Form tambah program studi.
     */
    public function create()
    {
        return view('prodi.create');
    }

    /**
     * Menyimpan data program studi baru.
     */
    public function store(ProdiRequest $request): RedirectResponse
    {
        Prodi::create($request->validated());

        return redirect()
            ->route('prodi.index')
            ->with('success', 'Program studi berhasil ditambahkan.');
    }

    /**
     * Menampilkan detail program studi beserta daftar mahasiswanya.
     */
    public function show(Prodi $prodi)
    {
        $mahasiswas = $prodi->mahasiswas()
            ->select('id', 'nim', 'nama', 'jenis_kelamin', 'prodi_id')
            ->orderBy('nama')
            ->paginate(15);

        return view('prodi.show', compact('prodi', 'mahasiswas'));
    }

    /**
     * Form edit program studi.
     */
    public function edit(Prodi $prodi)
    {
        return view('prodi.edit', compact('prodi'));
    }

    /**
     * Mengubah data program studi di database.
     */
    public function update(ProdiRequest $request, Prodi $prodi): RedirectResponse
    {
        $prodi->update($request->validated());

        return redirect()
            ->route('prodi.index')
            ->with('success', 'Program studi berhasil diperbarui.');
    }

    /**
     * Menghapus program studi (dengan validasi relasi mahasiswa).
     */
    public function destroy(Prodi $prodi)
    {
        if ($prodi->mahasiswas()->exists()) {
            return back()->with(
                'error',
                'Prodi tidak dapat dihapus karena masih digunakan mahasiswa.'
            );
        }

        $prodi->delete();

        return redirect()
            ->route('prodi.index')
            ->with('success', 'Program studi berhasil dihapus.');
    }
}
