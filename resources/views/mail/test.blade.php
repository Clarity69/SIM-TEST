<x-mail::message>
# Koneksi email berhasil

Email ini dikirim oleh perintah `php artisan mail:test` untuk memastikan pengaturan SMTP SIM Mahasiswa sudah benar.

<x-mail::panel>
Mailer: **{{ $mailer }}** · Host: **{{ $host }}:{{ $port }}**<br>
Waktu kirim: {{ now()->locale('id')->translatedFormat('d F Y, H:i') }}
</x-mail::panel>

Jika Anda menerima email ini, verifikasi akun, reset password, dan notifikasi sudah dapat dikirim.
</x-mail::message>
