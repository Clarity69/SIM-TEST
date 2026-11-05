@php
    /** @var \App\Models\Mahasiswa $mahasiswa */
    $inisial = collect(explode(' ', $mahasiswa->nama))->filter()->take(2)->map(fn ($kata) => mb_strtoupper(mb_substr($kata, 0, 1)))->implode('');
@endphp
<div class="kartu">
    <div class="kepala">
        @if ($logo)
            <img src="{{ $logo }}" alt="">
        @endif
        <div class="judul">
            <div class="kecil">Kartu Tanda Mahasiswa</div>
            <div class="besar">{{ config('stitek.nama') }}</div>
            <div class="kampus">{{ config('stitek.kop.fakultas') }} · {{ config('stitek.kop.kota') }}</div>
        </div>
        <div class="aksen"></div>
    </div>

    <div class="foto">
        @if ($foto)
            <img src="{{ $foto }}" alt="">
        @else
            <div class="inisial">{{ $inisial }}</div>
        @endif
    </div>

    <div class="data">
        <div class="nama">{{ \Illuminate\Support\Str::limit($mahasiswa->nama, 42) }}</div>
        <div class="nim">{{ $mahasiswa->nim }}</div>
        <table>
            <tr><td class="label">Prodi</td><td>{{ $mahasiswa->prodi->nama_prodi }}</td></tr>
            <tr><td class="label">Angkatan</td><td>{{ $mahasiswa->angkatan ?? '—' }}</td></tr>
            <tr><td class="label">Status</td><td><span class="status status-{{ $mahasiswa->status }}">{{ $mahasiswa->status }}</span></td></tr>
        </table>
    </div>

    <div class="qr">
        <img src="{{ $qr }}" alt="">
        <div class="ket">Pindai untuk<br>verifikasi</div>
    </div>

    <div class="kaki">
        <div class="kiri">{{ parse_url(config('stitek.kontak.website', 'https://stitek.ac.id'), PHP_URL_HOST) ?? 'stitek.ac.id' }}</div>
        <div class="kanan">Berlaku s.d. {{ $berlaku }}</div>
    </div>
</div>
