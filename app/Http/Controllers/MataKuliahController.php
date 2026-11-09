<?php

namespace App\Http\Controllers;

use App\Http\Requests\MataKuliahRequest;
use App\Models\MataKuliah;
use App\Models\Prodi;
use App\Services\AcademicStats;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class MataKuliahController extends Controller
{
    public function index(Request $request, AcademicStats $stats): View
    {
        $ready = $stats->get()['ready'];
        $query = MataKuliah::query()->with('prodi:id,nama_prodi');
        if ($request->filled('search')) {
            $search = '%'.addcslashes($request->string('search')->trim()->toString(), '%_\\').'%';
            $operator = $query->getConnection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
            $query->where(function ($query) use ($search, $operator): void {
                $query->where('nama', $operator, $search)->orWhere('kode', $operator, $search);
            });
        }
        if (in_array($request->query('aktif'), ['0', '1'], true)) {
            $query->where('aktif', $request->boolean('aktif'));
        }

        return view('mata-kuliah.index', [
            'ready' => $ready,
            'items' => $ready ? $query->orderBy('nama')->paginate(15)->withQueryString() : null,
        ]);
    }

    public function create(): View
    {
        return view('mata-kuliah.form', ['item' => null, 'prodis' => Prodi::opsi()]);
    }

    public function store(MataKuliahRequest $request): RedirectResponse
    {
        MataKuliah::create($request->validated());

        return redirect()->route('mata-kuliah.index')->with('success', 'Mata kuliah berhasil ditambahkan.');
    }

    public function edit(MataKuliah $mataKuliah): View
    {
        return view('mata-kuliah.form', ['item' => $mataKuliah, 'prodis' => Prodi::opsi()]);
    }

    public function update(MataKuliahRequest $request, MataKuliah $mataKuliah): RedirectResponse
    {
        $mataKuliah->update($request->validated());

        return redirect()->route('mata-kuliah.index')->with('success', 'Mata kuliah berhasil diperbarui.');
    }

    public function destroy(MataKuliah $mataKuliah): RedirectResponse
    {
        Gate::authorize('admin');
        $mataKuliah->delete();

        return redirect()->route('mata-kuliah.index')->with('success', 'Mata kuliah berhasil dihapus.');
    }
}
