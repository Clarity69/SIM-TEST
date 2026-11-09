<?php

namespace App\Services;

use App\Models\Mahasiswa;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdf;
use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Output\QROutputInterface;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

/**
 * Kartu mahasiswa (ukuran ID-1 / kartu ATM, 85,6 × 54 mm) dengan QR verifikasi.
 *
 * QR berisi signed URL ke halaman verifikasi publik, sehingga kartu bisa dicek
 * keasliannya tanpa login, dan tautannya tidak bisa ditebak/diubah ke mahasiswa lain.
 */
class KartuMahasiswa
{
    /** Ukuran kartu ID-1 dalam point (1 mm = 2,8346 pt). */
    private const KARTU_PT = [0, 0, 242.65, 153.07];

    /**
     * Satu kartu: PDF seukuran kartu. Banyak kartu: A4 berisi 2 × 5 kartu siap potong.
     *
     * @param  Collection<int, Mahasiswa>  $mahasiswas
     */
    public function pdf(Collection $mahasiswas): DomPdf
    {
        $mahasiswas->loadMissing('prodi:id,kode_prodi,singkatan,nama_prodi');
        $tunggal = $mahasiswas->count() === 1;

        $kartu = $mahasiswas->map(fn (Mahasiswa $mahasiswa) => [
            'mahasiswa' => $mahasiswa,
            'qr' => $this->qr($this->urlVerifikasi($mahasiswa)),
            'foto' => $this->foto($mahasiswa),
        ]);

        return Pdf::loadView('mahasiswa.kartu', [
            'kartu' => $kartu,
            'tunggal' => $tunggal,
            'logo' => $this->logo(),
            'berlaku' => now()->addYear()->endOfMonth()->locale('id')->translatedFormat('F Y'),
        ])->setPaper($tunggal ? self::KARTU_PT : 'a4', 'portrait');
    }

    /**
     * @param  Collection<int, Mahasiswa>  $mahasiswas
     */
    public function namaFile(Collection $mahasiswas): string
    {
        $mahasiswa = $mahasiswas->first();

        return $mahasiswas->count() === 1
            ? 'kartu-'.$mahasiswa->nim.'-'.Str::slug($mahasiswa->nama).'.pdf'
            : 'kartu-mahasiswa-'.$mahasiswas->count().'-'.now()->format('Ymd').'.pdf';
    }

    /**
     * Berlaku sampai akhir bulan yang tercetak pada kartu.
     */
    public function urlVerifikasi(Mahasiswa $mahasiswa): string
    {
        return URL::temporarySignedRoute('kartu.verifikasi', now()->addYear()->endOfMonth(), ['mahasiswa' => $mahasiswa->id]);
    }

    /**
     * QR PNG (data URI) yang bisa ditampilkan dompdf.
     */
    public function qr(string $isi): string
    {
        return (new QRCode(new QROptions([
            'outputType' => QROutputInterface::GDIMAGE_PNG,
            'eccLevel' => EccLevel::M,
            'scale' => 6,
            'quietzoneSize' => 1,
            'outputBase64' => true,
        ])))->render($isi);
    }

    /**
     * Foto WebP dikonversi ke JPEG karena dukungan WebP di dompdf bergantung build GD.
     */
    private function foto(Mahasiswa $mahasiswa): ?string
    {
        if (! $mahasiswa->foto || ! Storage::disk('public')->exists($mahasiswa->foto)) {
            return null;
        }

        $gambar = @imagecreatefromstring(Storage::disk('public')->get($mahasiswa->foto));

        if ($gambar === false) {
            return null;
        }

        ob_start();
        imagejpeg($gambar, null, 85);
        imagedestroy($gambar);

        return 'data:image/jpeg;base64,'.base64_encode((string) ob_get_clean());
    }

    private function logo(): ?string
    {
        $path = public_path(config('stitek.logo'));

        return is_file($path) ? 'data:'.mime_content_type($path).';base64,'.base64_encode(file_get_contents($path)) : null;
    }
}
