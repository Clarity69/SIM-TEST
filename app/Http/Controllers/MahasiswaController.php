<?php

namespace App\Http\Controllers;

use App\Http\Requests\MahasiswaRequest;
use App\Models\Mahasiswa;
use App\Models\Prodi;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class MahasiswaController extends Controller
{
    /**
     * Menampilkan daftar mahasiswa beserta pencarian, filter, sorting, dan pagination.
     */
    public function index(Request $request): View
    {
        $mahasiswas = Mahasiswa::query()
            ->select('id', 'nim', 'nama', 'jenis_kelamin', 'prodi_id', 'created_at')
            ->with('prodi:id,kode_prodi,singkatan,nama_prodi')
            ->filter($this->filters($request))
            ->paginate(10)
            ->withQueryString();

        $prodis = $this->prodiOptions();

        return view('mahasiswa.index', compact('mahasiswas', 'prodis'));
    }

    /**
     * Form tambah data mahasiswa.
     */
    public function create()
    {
        $prodis = $this->prodiOptions();

        return view('mahasiswa.create', compact('prodis'));
    }

    /**
     * Menyimpan data mahasiswa baru.
     */
    public function store(MahasiswaRequest $request): RedirectResponse
    {
        Mahasiswa::create($request->validated());

        return redirect()
            ->route('mahasiswa.index')
            ->with('success', 'Data mahasiswa berhasil ditambahkan.');
    }

    /**
     * Menampilkan detail mahasiswa.
     */
    public function show(Mahasiswa $mahasiswa)
    {
        $mahasiswa->load('prodi');

        return view('mahasiswa.show', compact('mahasiswa'));
    }

    /**
     * Form edit data mahasiswa.
     */
    public function edit(Mahasiswa $mahasiswa)
    {
        $prodis = $this->prodiOptions();

        return view('mahasiswa.edit', compact('mahasiswa', 'prodis'));
    }

    /**
     * Mengubah data mahasiswa.
     */
    public function update(MahasiswaRequest $request, Mahasiswa $mahasiswa): RedirectResponse
    {
        $mahasiswa->update($request->validated());

        return redirect()
            ->route('mahasiswa.index')
            ->with('success', 'Data mahasiswa berhasil diperbarui.');
    }

    /**
     * Menghapus data mahasiswa.
     */
    public function destroy(Mahasiswa $mahasiswa)
    {
        $mahasiswa->delete();

        return redirect()
            ->route('mahasiswa.index')
            ->with('success', 'Data mahasiswa berhasil dihapus.');
    }

    /**
     * Export laporan PDF mengikuti pencarian, filter, dan sorting yang aktif.
     */
    public function pdf(Request $request): Response
    {
        $mahasiswas = Mahasiswa::query()
            ->select('id', 'nim', 'nama', 'jenis_kelamin', 'tanggal_lahir', 'telepon', 'email', 'prodi_id', 'created_at')
            ->with('prodi:id,kode_prodi,singkatan,nama_prodi')
            ->filter($this->filters($request))
            ->get();

        $prodi = $request->filled('prodi_id') ? Prodi::find($request->integer('prodi_id')) : null;

        $pdf = Pdf::loadView('mahasiswa.pdf', [
            'mahasiswas' => $mahasiswas,
            'namaProdi' => $prodi?->nama_prodi ?? 'Semua Program Studi',
            'search' => $request->input('search'),
        ]);

        $pdf->setPaper('a4', 'landscape');

        return $pdf->stream('data-mahasiswa.pdf');
    }

    /**
     * Ambil parameter pencarian/filter/sorting dari query string.
     *
     * @return array{search: ?string, prodi_id: ?string, jenis_kelamin: ?string, sort: ?string, direction: ?string}
     */
    private function filters(Request $request): array
    {
        return [
            'search' => $request->string('search')->trim()->toString() ?: null,
            'prodi_id' => $request->input('prodi_id'),
            'jenis_kelamin' => $request->input('jenis_kelamin'),
            'sort' => $request->input('sort'),
            'direction' => $request->input('direction'),
        ];
    }

    /**
     * Daftar prodi untuk dropdown filter dan form (hanya kolom yang ditampilkan).
     *
     * @return Collection<int, Prodi>
     */
    private function prodiOptions(): Collection
    {
        return Prodi::query()
            ->select('id', 'kode_prodi', 'singkatan', 'nama_prodi')
            ->orderBy('nama_prodi')
            ->get();
    }
}
