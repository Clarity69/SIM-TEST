<?php

namespace App\Http\Controllers;

use App\Http\Requests\MahasiswaRequest;
use App\Mail\MahasiswaPdfMail;
use App\Models\Mahasiswa;
use App\Models\Prodi;
use App\Models\User;
use App\Notifications\MahasiswaChanged;
use App\Services\MahasiswaPdf;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\View\View;

class MahasiswaController extends Controller
{
    /**
     * Menampilkan daftar mahasiswa beserta pencarian, filter, sorting, dan pagination.
     */
    public function index(Request $request): View
    {
        $mahasiswas = $this->paginateDaftar($request);

        // Pencarian live (fetch) hanya butuh potongan tabel.
        if ($request->ajax()) {
            return view('mahasiswa._table', compact('mahasiswas'));
        }

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
        $mahasiswa = Mahasiswa::create($request->validated());

        $this->notifyAdmins('ditambahkan', $mahasiswa, $request);

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
    public function destroy(Request $request, Mahasiswa $mahasiswa): RedirectResponse
    {
        $mahasiswa->delete();

        $this->notifyAdmins('dihapus', $mahasiswa, $request);

        return redirect()
            ->route('mahasiswa.index')
            ->with('success', 'Data mahasiswa berhasil dihapus.');
    }

    /**
     * Tampilkan laporan PDF mengikuti pencarian, filter, dan sorting yang aktif.
     */
    public function pdf(Request $request, MahasiswaPdf $pdf): Response
    {
        $filters = $this->filters($request);

        return $pdf->daftar($filters)->stream($pdf->namaFileDaftar($filters));
    }

    /**
     * Tampilkan PDF biodata satu mahasiswa.
     */
    public function pdfShow(Mahasiswa $mahasiswa, MahasiswaPdf $pdf): Response
    {
        return $pdf->detail($mahasiswa)->stream($pdf->namaFileDetail($mahasiswa));
    }

    /**
     * Kirim PDF daftar (sesuai filter aktif) ke email, diproses lewat queue.
     */
    public function emailPdf(Request $request): RedirectResponse
    {
        $email = $this->recipient($request);

        Mail::to($email)->queue(new MahasiswaPdfMail($request->user()->name, $this->filters($request)));

        return back()->with('success', "Laporan PDF sedang dikirim ke {$email}.");
    }

    /**
     * Kirim PDF biodata satu mahasiswa ke email, diproses lewat queue.
     */
    public function emailPdfShow(Request $request, Mahasiswa $mahasiswa): RedirectResponse
    {
        $email = $this->recipient($request);

        Mail::to($email)->queue(new MahasiswaPdfMail($request->user()->name, mahasiswa: $mahasiswa));

        return back()->with('success', "Biodata {$mahasiswa->nama} sedang dikirim ke {$email}.");
    }

    /**
     * Alamat tujuan: input `email` bila diisi, selain itu email user yang login.
     */
    private function recipient(Request $request): string
    {
        $validated = $request->validate(['email' => ['nullable', 'email', 'max:255']]);

        return $validated['email'] ?? $request->user()->email;
    }

    /**
     * Beri tahu user yang mengaktifkan notifikasi perubahan data (lewat queue).
     */
    private function notifyAdmins(string $aksi, Mahasiswa $mahasiswa, Request $request): void
    {
        $penerima = User::query()->where('notify_mahasiswa_changes', true)->get();

        if ($penerima->isEmpty()) {
            return;
        }

        Notification::send($penerima, new MahasiswaChanged($aksi, [
            'id' => $mahasiswa->id,
            'nim' => $mahasiswa->nim,
            'nama' => $mahasiswa->nama,
            'jenis_kelamin' => $mahasiswa->jenis_kelamin,
            'prodi' => $mahasiswa->prodi()->value('nama_prodi'),
        ], $request->user()->name));
    }

    /**
     * Daftar mahasiswa per halaman dalam SATU query: data + prodi (JOIN) + total
     * (COUNT(*) OVER()). Tiap query ke Supabase ±0,3 detik, jadi menggabungkan
     * count, data, dan eager load prodi menghemat dua round trip per pindah halaman.
     *
     * @return LengthAwarePaginator<int, Mahasiswa>
     */
    private function paginateDaftar(Request $request, int $perPage = 10): LengthAwarePaginator
    {
        $page = max(1, $request->integer('page', 1));

        $baris = Mahasiswa::query()
            ->join('prodis', 'prodis.id', '=', 'mahasiswas.prodi_id')
            ->select('mahasiswas.id', 'mahasiswas.nim', 'mahasiswas.nama', 'mahasiswas.jenis_kelamin', 'mahasiswas.prodi_id', 'mahasiswas.created_at')
            ->addSelect('prodis.kode_prodi as prodi_kode', 'prodis.singkatan as prodi_singkatan', 'prodis.nama_prodi as prodi_nama')
            ->selectRaw('COUNT(*) OVER () AS total_baris')
            ->filter($this->filters($request))
            ->forPage($page, $perPage)
            ->get();

        // Halaman di luar jangkauan tidak membawa total; hitung terpisah (jarang terjadi).
        $total = $baris->isNotEmpty()
            ? (int) $baris->first()->total_baris
            : ($page > 1 ? Mahasiswa::query()->filter($this->filters($request))->count() : 0);

        $baris->each(function (Mahasiswa $mahasiswa) {
            $prodi = (new Prodi)->forceFill([
                'id' => $mahasiswa->prodi_id,
                'kode_prodi' => $mahasiswa->prodi_kode,
                'singkatan' => $mahasiswa->prodi_singkatan,
                'nama_prodi' => $mahasiswa->prodi_nama,
            ]);
            $prodi->exists = true;
            $prodi->syncOriginal();

            $mahasiswa->setRelation('prodi', $prodi);
            unset($mahasiswa->prodi_kode, $mahasiswa->prodi_singkatan, $mahasiswa->prodi_nama, $mahasiswa->total_baris);
            $mahasiswa->syncOriginal();
        });

        return (new LengthAwarePaginator($baris, $total, $perPage, $page, [
            'path' => $request->url(),
            'pageName' => 'page',
        ]))->withQueryString();
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
