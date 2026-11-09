<?php

namespace App\Http\Controllers;

use App\Http\Requests\DosenRequest;
use App\Models\Dosen;
use App\Models\Prodi;
use App\Services\AcademicStats;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class DosenController extends Controller
{
    public function index(Request $request, AcademicStats $stats): View
    {
        $ready = $stats->get()['ready'];
        $query = Dosen::query()->with('prodi:id,nama_prodi');
        if ($request->filled('search')) {
            $search = '%'.addcslashes($request->string('search')->trim()->toString(), '%_\\').'%';
            $operator = $query->getConnection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
            $query->where(function ($query) use ($search, $operator): void {
                $query->where('nama', $operator, $search)->orWhere('nidn', $operator, $search);
            });
        }
        if (in_array($request->query('aktif'), ['0', '1'], true)) {
            $query->where('aktif', $request->boolean('aktif'));
        }

        return view('dosen.index', [
            'ready' => $ready,
            'items' => $ready ? $query->orderBy('nama')->paginate(15)->withQueryString() : null,
        ]);
    }

    public function create(): View
    {
        return view('dosen.form', ['item' => null, 'prodis' => Prodi::opsi()]);
    }

    public function store(DosenRequest $request): RedirectResponse
    {
        Dosen::create($request->validated());

        return redirect()->route('dosen.index')->with('success', 'Dosen berhasil ditambahkan.');
    }

    public function edit(Dosen $dosen): View
    {
        return view('dosen.form', ['item' => $dosen, 'prodis' => Prodi::opsi()]);
    }

    public function update(DosenRequest $request, Dosen $dosen): RedirectResponse
    {
        $dosen->update($request->validated());

        return redirect()->route('dosen.index')->with('success', 'Dosen berhasil diperbarui.');
    }

    public function destroy(Dosen $dosen): RedirectResponse
    {
        Gate::authorize('admin');
        $dosen->delete();

        return redirect()->route('dosen.index')->with('success', 'Dosen berhasil dihapus.');
    }
}
