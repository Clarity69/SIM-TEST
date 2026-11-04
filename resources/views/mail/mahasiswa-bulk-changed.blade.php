<x-mail::message>
# {{ $jumlah }} mahasiswa {{ $aksi }}

**{{ $oleh }}** baru saja melakukan perubahan massal pada data mahasiswa ({{ now()->locale('id')->translatedFormat('d F Y, H:i') }}).

@if ($contoh)
<x-mail::panel>
@foreach ($contoh as $item)
- {{ $item }}
@endforeach
@if ($jumlah > count($contoh))
- dan {{ $jumlah - count($contoh) }} lainnya
@endif
</x-mail::panel>
@endif

<x-mail::button :url="$url">
Buka Data Mahasiswa
</x-mail::button>

<x-slot:subcopy>
Anda menerima email ini karena notifikasi perubahan data aktif. Matikan di [halaman Profil]({{ $pengaturanUrl }}).
</x-slot:subcopy>
</x-mail::message>
