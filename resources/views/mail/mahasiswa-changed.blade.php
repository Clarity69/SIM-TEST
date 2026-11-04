<x-mail::message>
# Data mahasiswa {{ $aksi }}

**{{ $oleh }}** baru saja {{ $aksi === 'ditambahkan' ? 'menambahkan' : 'menghapus' }} data mahasiswa berikut:

<x-mail::table>
| | |
|:--|:--|
| NIM | **{{ $mahasiswa['nim'] }}** |
| Nama | {{ $mahasiswa['nama'] }} |
| Jenis kelamin | {{ $mahasiswa['jenis_kelamin'] }} |
| Program studi | {{ $mahasiswa['prodi'] ?? '-' }} |
| Waktu | {{ now()->locale('id')->translatedFormat('d F Y, H:i') }} |
</x-mail::table>

<x-mail::button :url="$url">
{{ $aksi === 'ditambahkan' ? 'Lihat Data' : 'Buka Data Mahasiswa' }}
</x-mail::button>

<x-slot:subcopy>
Anda menerima email ini karena notifikasi perubahan data aktif. Matikan di [halaman Profil]({{ $pengaturanUrl }}).
</x-slot:subcopy>
</x-mail::message>
