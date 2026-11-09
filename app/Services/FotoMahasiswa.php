<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;

/**
 * Menyimpan foto mahasiswa yang sudah dikompres: dipotong persegi di tengah,
 * diperkecil ke 400x400, lalu disimpan sebagai WebP (±20–40 KB) di disk public.
 */
class FotoMahasiswa
{
    public const UKURAN = 400;

    public const FOLDER = 'foto-mahasiswa';

    public function simpan(UploadedFile $file): string
    {
        $sumber = @imagecreatefromstring((string) file_get_contents($file->getRealPath()));

        if ($sumber === false) {
            throw new RuntimeException('File foto tidak dapat dibaca sebagai gambar.');
        }

        $lebar = imagesx($sumber);
        $tinggi = imagesy($sumber);
        $sisi = min($lebar, $tinggi);

        $hasil = imagecreatetruecolor(self::UKURAN, self::UKURAN);
        imagecopyresampled(
            $hasil, $sumber,
            0, 0,
            intdiv($lebar - $sisi, 2), intdiv($tinggi - $sisi, 2),
            self::UKURAN, self::UKURAN,
            $sisi, $sisi,
        );

        ob_start();
        $encoded = imagewebp($hasil, null, 80);
        $data = (string) ob_get_clean();

        if (! $encoded || $data === '') {
            throw new RuntimeException('Foto tidak dapat dikonversi ke WebP.');
        }

        $path = self::FOLDER.'/'.Str::uuid().'.webp';
        if (! Storage::disk('public')->put($path, $data)) {
            throw ValidationException::withMessages(['foto' => 'Foto tidak dapat disimpan. Silakan coba lagi.']);
        }

        return $path;
    }

    public function hapus(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }
}
