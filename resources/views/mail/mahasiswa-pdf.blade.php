<x-mail::message>
# {{ $mahasiswa ? 'Biodata mahasiswa' : 'Laporan data mahasiswa' }}

**{{ $pengirim }}** mengirimkan laporan PDF dari SIM Mahasiswa {{ config('stitek.singkatan') }}. File terlampir pada email ini.

@if ($mahasiswa)
<x-mail::panel>
{{ $mahasiswa->nim }} · **{{ $mahasiswa->nama }}**
</x-mail::panel>
@elseif (array_filter($filters))
<x-mail::panel>
Laporan dibuat sesuai filter yang aktif saat dikirim
@if (filled($filters['search'] ?? null))
· kata kunci "{{ $filters['search'] }}"
@endif
@if (filled($filters['jenis_kelamin'] ?? null))
· {{ $filters['jenis_kelamin'] }}
@endif
</x-mail::panel>
@endif

Dokumen ini dibuat otomatis pada {{ now()->locale('id')->translatedFormat('d F Y, H:i') }}.
</x-mail::message>
