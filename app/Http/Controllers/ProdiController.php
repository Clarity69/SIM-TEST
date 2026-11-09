<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProdiRequest;
use App\Models\Mahasiswa;
use App\Models\Prodi;
use App\Services\AcademicStats;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;

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

        $page = max(1, $request->integer('page', 1));
        $cacheKey = 'prodi.daftar.'.Mahasiswa::versiDaftar().'.'.md5(json_encode([$request->query('search'), $page]));
        $data = Cache::remember($cacheKey, 300, fn (): array => [
            'rows' => (clone $query)->orderBy('nama_prodi')->forPage($page, 10)->get()
                ->map(fn (Prodi $prodi): array => $prodi->getAttributes())->all(),
            'total' => (clone $query)->count(),
        ]);
        $prodis = (new LengthAwarePaginator(Prodi::hydrate($data['rows']), $data['total'], 10, $page, [
            'path' => $request->url(),
        ]))->withQueryString();

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
        Gate::authorize('delete', $prodi);

        // Termasuk mahasiswa di Sampah: foreign key tetap menunjuk ke prodi ini.
        if ($prodi->mahasiswas()->withTrashed()->exists()) {
            return back()->with(
                'error',
                'Prodi tidak dapat dihapus karena masih digunakan mahasiswa (termasuk yang ada di Sampah).'
            );
        }

        if (app(AcademicStats::class)->get()['ready'] && ($prodi->dosens()->exists() || $prodi->mataKuliahs()->exists())) {
            return back()->with('error', 'Prodi tidak dapat dihapus karena masih digunakan dosen atau mata kuliah.');
        }

        $prodi->delete();

        return redirect()
            ->route('prodi.index')
            ->with('success', 'Program studi berhasil dihapus.');
    }
}
