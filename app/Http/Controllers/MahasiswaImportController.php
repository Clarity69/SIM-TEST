<?php

namespace App\Http\Controllers;

use App\Exports\MahasiswaTemplateExport;
use App\Exports\SheetArrayExport;
use App\Models\ActivityLog;
use App\Models\User;
use App\Notifications\MahasiswaBulkChanged;
use App\Services\ImportMahasiswa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

/**
 * Wizard impor mahasiswa: 1) unggah, 2) pratinjau & validasi, 3) hasil.
 * Hasil baca/validasi disimpan di cache (30 menit) per token & per user.
 */
class MahasiswaImportController extends Controller
{
    private const TTL = 1800;

    public function create(): View
    {
        return view('mahasiswa.import.create');
    }

    public function template(): BinaryFileResponse
    {
        return Excel::download(new MahasiswaTemplateExport, 'template-impor-mahasiswa.xlsx');
    }

    public function upload(Request $request, ImportMahasiswa $import): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv,txt', 'max:5120'],
        ], [], ['file' => 'file']);

        try {
            $hasil = $import->validasi($import->baca($request->file('file')));
        } catch (RuntimeException $e) {
            return back()->withErrors(['file' => $e->getMessage()]);
        } catch (Throwable $e) {
            report($e);

            return back()->withErrors(['file' => 'File tidak dapat dibaca. Pastikan formatnya .xlsx atau .csv sesuai template.']);
        }

        $token = (string) Str::uuid();
        Cache::put($this->kunci($token), [
            'user_id' => $request->user()->id,
            'nama_file' => $request->file('file')->getClientOriginalName(),
            'ukuran' => $request->file('file')->getSize(),
            'valid' => $hasil['valid'],
            'invalid' => $hasil['invalid'],
            'hasil' => null,
        ], self::TTL);

        return redirect()->route('mahasiswa.import.preview', $token);
    }

    public function preview(Request $request, string $token): View
    {
        return view('mahasiswa.import.preview', ['token' => $token, 'sesi' => $this->sesi($request, $token)]);
    }

    public function store(Request $request, string $token, ImportMahasiswa $import): RedirectResponse
    {
        $sesi = $this->sesi($request, $token);

        if ($sesi['hasil'] !== null) {
            return redirect()->route('mahasiswa.import.preview', $token);
        }

        if ($sesi['valid'] === []) {
            return back()->with('error', 'Tidak ada baris valid untuk diimpor.');
        }

        $hasil = $import->simpan($sesi['valid']);
        $sesi['hasil'] = $hasil;
        Cache::put($this->kunci($token), $sesi, self::TTL);

        if ($hasil['berhasil'] > 0) {
            ActivityLog::catat('import', "Mengimpor {$hasil['berhasil']} mahasiswa dari {$sesi['nama_file']}", properties: [
                'berhasil' => $hasil['berhasil'],
                'dilewati' => $hasil['dilewati'],
                'contoh' => $hasil['contoh'],
            ]);
            $admin = User::query()->where('role', User::ROLE_ADMIN)->where('notify_mahasiswa_changes', true)->get();
            Notification::send($admin, new MahasiswaBulkChanged('ditambahkan lewat impor', $hasil['berhasil'], $hasil['contoh'], $request->user()->name));
        }

        return redirect()->route('mahasiswa.import.preview', $token)
            ->with('success', "{$hasil['berhasil']} mahasiswa berhasil diimpor.");
    }

    /**
     * Laporan baris bermasalah (CSV) agar file bisa diperbaiki lalu diimpor ulang.
     */
    public function report(Request $request, string $token): BinaryFileResponse
    {
        $sesi = $this->sesi($request, $token);
        $rows = [['baris', 'nim', 'nama', 'jenis_kelamin', 'prodi', 'angkatan', 'status', 'kesalahan']];

        foreach ($sesi['invalid'] as $item) {
            $rows[] = [
                $item['baris'], $item['data']['nim'], $item['data']['nama'], $item['data']['jenis_kelamin'],
                $item['data']['prodi'], $item['data']['angkatan'], $item['data']['status'], implode(' ', $item['errors']),
            ];
        }

        return Excel::download(new SheetArrayExport('baris-bermasalah', $rows), 'baris-bermasalah-impor.csv', ExcelFormat::CSV);
    }

    /**
     * @return array{user_id: int, nama_file: string, ukuran: int, valid: list<array<string, mixed>>, invalid: list<array<string, mixed>>, hasil: ?array<string, mixed>}
     */
    private function sesi(Request $request, string $token): array
    {
        $sesi = Cache::get($this->kunci($token));

        abort_if(! is_array($sesi) || $sesi['user_id'] !== $request->user()->id, 404, 'Sesi impor tidak ditemukan atau sudah kedaluwarsa.');

        return $sesi;
    }

    private function kunci(string $token): string
    {
        return 'import.mahasiswa.'.$token;
    }
}
