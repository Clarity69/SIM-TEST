<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Biodata {{ $mahasiswa->nama }}</title>
    @include('pdf._styles')
</head>
<body>
    @include('pdf._footer')
    @include('pdf._kop')

    <div class="judul">
        <h1>BIODATA MAHASISWA</h1>
        <p>{{ $mahasiswa->prodi->nama_prodi }}</p>
    </div>

    @php
        $fotoPath = $mahasiswa->foto ? storage_path('app/public/'.$mahasiswa->foto) : null;
        $foto = $fotoPath && is_file($fotoPath) ? 'data:image/webp;base64,'.base64_encode(file_get_contents($fotoPath)) : null;
    @endphp
    @if ($foto)
        <div style="text-align: center; margin-bottom: 10px;">
            <img src="{{ $foto }}" alt="Foto" style="width: 3cm; height: 3cm; border: 1px solid #999;">
        </div>
    @endif

    <table class="biodata">
        <tr><td class="label">NIM</td><td class="titik">:</td><td><strong>{{ $mahasiswa->nim }}</strong></td></tr>
        <tr><td class="label">Nama Lengkap</td><td class="titik">:</td><td><strong>{{ $mahasiswa->nama }}</strong></td></tr>
        <tr><td class="label">Jenis Kelamin</td><td class="titik">:</td><td>{{ $mahasiswa->jenis_kelamin }}</td></tr>
        <tr><td class="label">Tanggal Lahir</td><td class="titik">:</td><td>{{ $mahasiswa->tanggal_lahir?->locale('id')->translatedFormat('d F Y') ?? '-' }}</td></tr>
        <tr><td class="label">Program Studi</td><td class="titik">:</td><td>{{ $mahasiswa->prodi->nama_prodi }} ({{ $mahasiswa->prodi->kode_prodi }})</td></tr>
        <tr><td class="label">Angkatan</td><td class="titik">:</td><td>{{ $mahasiswa->angkatan ?? '-' }}</td></tr>
        <tr><td class="label">Status</td><td class="titik">:</td><td>{{ $mahasiswa->status }}</td></tr>
        <tr><td class="label">Alamat</td><td class="titik">:</td><td>{{ $mahasiswa->alamat ?: '-' }}</td></tr>
        <tr><td class="label">Telepon</td><td class="titik">:</td><td>{{ $mahasiswa->telepon ?: '-' }}</td></tr>
        <tr><td class="label">Email</td><td class="titik">:</td><td>{{ $mahasiswa->email ?: '-' }}</td></tr>
        <tr><td class="label">Terdaftar di sistem</td><td class="titik">:</td><td>{{ $mahasiswa->created_at?->locale('id')->translatedFormat('d F Y') ?? '-' }}</td></tr>
    </table>

    @include('pdf._ttd')
</body>
</html>
