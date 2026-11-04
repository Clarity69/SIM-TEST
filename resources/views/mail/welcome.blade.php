<x-mail::message>
# Halo, {{ $nama }}!

Akun Anda di **SIM Mahasiswa {{ config('stitek.singkatan') }}** sudah dibuat. Setelah memverifikasi email, Anda dapat:

- melihat statistik mahasiswa per program studi,
- mencari, menambah, dan memperbarui data mahasiswa,
- mencetak atau mengirim laporan PDF ber-kop resmi kampus.

<x-mail::button :url="$url">
Buka Dashboard
</x-mail::button>

Jika Anda tidak merasa mendaftar, abaikan email ini.

Salam,<br>
Tim SIM Mahasiswa {{ config('stitek.singkatan') }}
</x-mail::message>
