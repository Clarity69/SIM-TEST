<?php

namespace App\Http\Controllers;

use App\Exports\MahasiswaExport;
use App\Http\Requests\MahasiswaRequest;
use App\Mail\MahasiswaPdfMail;
use App\Models\ActivityLog;
use App\Models\Mahasiswa;
use App\Models\Prodi;
use App\Models\User;
use App\Notifications\MahasiswaBulkChanged;
use App\Notifications\MahasiswaChanged;
use App\Services\DashboardStats;
use App\Services\FotoMahasiswa;
use App\Services\KartuMahasiswa;
use App\Services\MahasiswaPdf;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

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
        $angkatans = Mahasiswa::opsiAngkatan();

        return view('mahasiswa.index', compact('mahasiswas', 'prodis', 'angkatans'));
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
    public function store(MahasiswaRequest $request, FotoMahasiswa $foto): RedirectResponse
    {
        $mahasiswa = new Mahasiswa($request->safe()->except(['foto', 'hapus_foto']));

        if ($request->hasFile('foto')) {
            $mahasiswa->foto = $foto->simpan($request->file('foto'));
        }

        try {
            DB::transaction(fn () => $mahasiswa->save());
        } catch (Throwable $exception) {
            $foto->hapus($mahasiswa->foto);

            throw $exception;
        }

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

        $riwayat = ActivityLog::query()
            ->where('subject_type', 'mahasiswa')
            ->where('subject_id', $mahasiswa->id)
            ->latest('created_at')
            ->latest('id')
            ->limit(8)
            ->get();
        $namaProdi = Prodi::opsi()->pluck('nama_prodi', 'id')->all();

        return view('mahasiswa.show', compact('mahasiswa', 'riwayat', 'namaProdi'));
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
    public function update(MahasiswaRequest $request, Mahasiswa $mahasiswa, FotoMahasiswa $foto): RedirectResponse
    {
        $mahasiswa->fill($request->safe()->except(['foto', 'hapus_foto']));
        $fotoLama = $mahasiswa->foto;

        if ($request->hasFile('foto')) {
            $mahasiswa->foto = $foto->simpan($request->file('foto'));
        } elseif ($request->boolean('hapus_foto')) {
            $mahasiswa->foto = null;
        }

        try {
            DB::transaction(fn () => $mahasiswa->save());
        } catch (Throwable $exception) {
            if ($mahasiswa->foto !== $fotoLama) {
                $foto->hapus($mahasiswa->foto);
            }

            throw $exception;
        }

        if ($fotoLama && $fotoLama !== $mahasiswa->foto) {
            $foto->hapus($fotoLama);
        }

        return redirect()
            ->route('mahasiswa.index')
            ->with('success', 'Data mahasiswa berhasil diperbarui.');
    }

    /**
     * Menghapus data mahasiswa.
     */
    public function destroy(Request $request, Mahasiswa $mahasiswa): RedirectResponse
    {
        // Dipindah ke Sampah (soft delete); catat siapa yang menghapus.
        $mahasiswa->forceFill(['deleted_by' => $request->user()->id])->saveQuietly();
        $mahasiswa->delete();

        $this->notifyAdmins('dihapus', $mahasiswa, $request);

        return redirect()
            ->route('mahasiswa.index')
            ->with('success', 'Data mahasiswa dipindahkan ke Sampah.');
    }

    /**
     * Aksi massal dari tabel: hapus (ke Sampah), ubah status, atau ekspor baris terpilih.
     */
    public function bulk(Request $request): RedirectResponse|BinaryFileResponse|Response
    {
        $validated = $request->validate([
            'aksi' => ['required', 'in:hapus,status,export,kartu'],
            'ids' => ['required', 'array', 'min:1', 'max:500'],
            'ids.*' => ['integer'],
            'status' => ['required_if:aksi,status', 'nullable', Rule::in(array_keys(Mahasiswa::STATUS))],
        ], ['ids.required' => 'Pilih minimal satu mahasiswa.']);

        $ids = array_map('intval', $validated['ids']);

        if ($validated['aksi'] === 'kartu') {
            if (count($ids) > 100) {
                return back()->with('error', 'Cetak kartu maksimal 100 mahasiswa sekaligus.');
            }

            $mahasiswas = Mahasiswa::query()->whereKey($ids)->orderBy('nim')->get();
            $kartu = app(KartuMahasiswa::class);
            ActivityLog::catat('export', "Mencetak kartu {$mahasiswas->count()} mahasiswa", properties: ['ids' => $ids]);

            return $kartu->pdf($mahasiswas)->download($kartu->namaFile($mahasiswas));
        }

        if ($validated['aksi'] === 'export') {
            ActivityLog::catat('export', 'Mengekspor '.count($ids).' mahasiswa terpilih ke Excel', properties: ['jumlah' => count($ids)]);

            return Excel::download(new MahasiswaExport(ids: $ids), 'mahasiswa-terpilih-'.now()->format('Ymd-His').'.xlsx');
        }

        if ($validated['aksi'] === 'status') {
            $jumlah = Mahasiswa::query()->whereKey($ids)->update(['status' => $validated['status'], 'updated_at' => now()]);
            DashboardStats::forget();
            Mahasiswa::bumpVersiDaftar();
            ActivityLog::catat('bulk', "Mengubah status {$jumlah} mahasiswa menjadi {$validated['status']}", properties: ['ids' => $ids, 'status' => $validated['status']]);

            return back()->with('success', "Status {$jumlah} mahasiswa diubah menjadi {$validated['status']}.");
        }

        $mahasiswas = Mahasiswa::query()->whereKey($ids)->get(['id', 'nim', 'nama', 'jenis_kelamin', 'prodi_id']);

        ActivityLog::tanpaCatat(function () use ($mahasiswas, $request) {
            foreach ($mahasiswas as $mahasiswa) {
                $mahasiswa->forceFill(['deleted_by' => $request->user()->id])->saveQuietly();
                $mahasiswa->delete();
            }
        });

        if ($mahasiswas->isNotEmpty()) {
            ActivityLog::catat('bulk', "Memindahkan {$mahasiswas->count()} mahasiswa ke Sampah", properties: [
                'ids' => $mahasiswas->modelKeys(),
                'contoh' => $mahasiswas->take(5)->map(fn ($m) => "{$m->nama} ({$m->nim})")->all(),
            ]);

            $admin = User::query()->where('role', User::ROLE_ADMIN)->where('notify_mahasiswa_changes', true)->get();
            Notification::send($admin, new MahasiswaBulkChanged(
                'dipindahkan ke Sampah',
                $mahasiswas->count(),
                $mahasiswas->take(5)->map(fn ($m) => "{$m->nama} ({$m->nim})")->all(),
                $request->user()->name,
            ));
        }

        return back()->with('success', "{$mahasiswas->count()} mahasiswa dipindahkan ke Sampah.");
    }

    /**
     * Tampilkan laporan PDF mengikuti pencarian, filter, dan sorting yang aktif.
     */
    public function pdf(Request $request, MahasiswaPdf $pdf): Response
    {
        $filters = $this->filters($request);
        ActivityLog::catat('export', 'Mencetak PDF daftar mahasiswa', properties: ['filter' => array_filter($filters)]);

        return $pdf->daftar($filters)->stream($pdf->namaFileDaftar($filters));
    }

    /**
     * Tampilkan PDF biodata satu mahasiswa.
     */
    public function pdfShow(Mahasiswa $mahasiswa, MahasiswaPdf $pdf): Response
    {
        ActivityLog::catat('export', "Mencetak PDF biodata {$mahasiswa->nama}", $mahasiswa);

        return $pdf->detail($mahasiswa)->stream($pdf->namaFileDetail($mahasiswa));
    }

    /**
     * Kirim PDF daftar (sesuai filter aktif) ke email, diproses lewat queue.
     */
    public function emailPdf(Request $request, MahasiswaPdf $pdf): RedirectResponse
    {
        $email = $this->recipient($request);
        $filters = $this->filters($request);
        $pdf->validateSize($filters);

        Mail::to($email)->queue(new MahasiswaPdfMail($request->user()->name, $filters));

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
     * Beri tahu admin yang mengaktifkan notifikasi perubahan data (lewat queue).
     */
    private function notifyAdmins(string $aksi, Mahasiswa $mahasiswa, Request $request): void
    {
        $penerima = User::query()
            ->where('role', User::ROLE_ADMIN)
            ->where('notify_mahasiswa_changes', true)
            ->get();

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
        $filters = $this->filters($request);

        // Hasil per halaman/filter di-cache sebagai array; kunci memuat versi data sehingga
        // otomatis usang begitu ada mahasiswa/prodi yang berubah (Mahasiswa::versiDaftar()).
        $kunci = 'mahasiswa.daftar.'.Mahasiswa::versiDaftar().'.'.md5(json_encode([$filters, $page, $perPage]));

        ['rows' => $rows, 'total' => $total] = Cache::remember($kunci, 600, function () use ($filters, $page, $perPage) {
            $rows = Mahasiswa::query()
                ->join('prodis', 'prodis.id', '=', 'mahasiswas.prodi_id')
                ->select('mahasiswas.id', 'mahasiswas.nim', 'mahasiswas.nama', 'mahasiswas.jenis_kelamin', 'mahasiswas.prodi_id', 'mahasiswas.angkatan', 'mahasiswas.status', 'mahasiswas.foto', 'mahasiswas.created_at')
                ->addSelect('prodis.kode_prodi as prodi_kode', 'prodis.singkatan as prodi_singkatan', 'prodis.nama_prodi as prodi_nama')
                ->selectRaw('COUNT(*) OVER () AS total_baris')
                ->filter($filters)
                ->forPage($page, $perPage)
                ->toBase()
                ->get()
                ->map(fn ($row) => (array) $row)
                ->all();

            // Halaman di luar jangkauan tidak membawa total; hitung terpisah (jarang terjadi).
            $total = $rows !== []
                ? (int) $rows[0]['total_baris']
                : ($page > 1 ? Mahasiswa::query()->filter($filters)->count() : 0);

            return ['rows' => $rows, 'total' => $total];
        });

        $baris = collect($rows)->map(function (array $row) {
            $prodi = (new Prodi)->newFromBuilder([
                'id' => $row['prodi_id'],
                'kode_prodi' => $row['prodi_kode'],
                'singkatan' => $row['prodi_singkatan'],
                'nama_prodi' => $row['prodi_nama'],
            ]);

            $mahasiswa = (new Mahasiswa)->newFromBuilder(collect($row)->except(['prodi_kode', 'prodi_singkatan', 'prodi_nama', 'total_baris'])->all());

            return $mahasiswa->setRelation('prodi', $prodi);
        });

        return (new LengthAwarePaginator($baris, $total, $perPage, $page, [
            'path' => $request->url(),
            'pageName' => 'page',
        ]))->withQueryString();
    }

    /**
     * Ambil parameter pencarian/filter/sorting dari query string.
     *
     * @return array{search: ?string, prodi_id: ?string, jenis_kelamin: ?string, status: ?string, angkatan: ?string, sort: ?string, direction: ?string}
     */
    private function filters(Request $request): array
    {
        return [
            'search' => $request->string('search')->trim()->toString() ?: null,
            'prodi_id' => $request->input('prodi_id'),
            'jenis_kelamin' => $request->input('jenis_kelamin'),
            'status' => $request->input('status'),
            'angkatan' => $request->input('angkatan'),
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
        return Prodi::opsi();
    }
}
