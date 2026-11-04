<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Mahasiswa</title>
    @include('pdf._styles')
</head>
<body>

    @include('pdf._footer')

    @include('pdf._kop')

    {{-- ================= JUDUL ================= --}}
    <div class="judul">
        <h1>DAFTAR DATA MAHASISWA</h1>
        <p>Tahun Akademik {{ now()->month >= 8 ? now()->year . '/' . (now()->year + 1) : (now()->year - 1) . '/' . now()->year }}</p>
    </div>

    <table class="info">
        <tr>
            <td class="label">Program Studi</td><td class="titik">:</td>
            <td>{{ $namaProdi }}</td>
        </tr>
        @if (filled($filter['search']))
            <tr>
                <td class="label">Kata Kunci</td><td class="titik">:</td>
                <td>"{{ $filter['search'] }}"</td>
            </tr>
        @endif
        @if (filled($filter['jenis_kelamin']))
            <tr>
                <td class="label">Jenis Kelamin</td><td class="titik">:</td>
                <td>{{ $filter['jenis_kelamin'] }}</td>
            </tr>
        @endif
        <tr>
            <td class="label">Jumlah Data</td><td class="titik">:</td>
            <td>{{ $mahasiswas->count() }} mahasiswa</td>
        </tr>
    </table>

    {{-- ================= TABEL DATA ================= --}}
    <table class="data">
        <thead>
            <tr>
                <th style="width: 4%;">No</th>
                <th style="width: 12%;">NIM</th>
                <th style="width: 22%;">Nama Mahasiswa</th>
                <th style="width: 6%;">L/P</th>
                <th style="width: 11%;">Tanggal Lahir</th>
                <th style="width: 18%;">Program Studi</th>
                <th style="width: 12%;">Telepon</th>
                <th style="width: 15%;">Email</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($mahasiswas as $mhs)
                <tr>
                    <td class="tengah">{{ $loop->iteration }}</td>
                    <td class="tengah">{{ $mhs->nim }}</td>
                    <td>{{ $mhs->nama }}</td>
                    <td class="tengah">{{ $mhs->jenis_kelamin === 'Laki-laki' ? 'L' : 'P' }}</td>
                    <td class="tengah">
                        {{ $mhs->tanggal_lahir?->locale('id')->translatedFormat('d M Y') ?? '-' }}
                    </td>
                    <td>{{ $mhs->prodi->nama_prodi ?? '-' }}</td>
                    <td>{{ $mhs->telepon ?: '-' }}</td>
                    <td>{{ $mhs->email ?: '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="kosong">Tidak ada data mahasiswa.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="ringkasan">
        Keterangan: L = Laki-laki ({{ $mahasiswas->where('jenis_kelamin', 'Laki-laki')->count() }} orang), P = Perempuan ({{ $mahasiswas->where('jenis_kelamin', 'Perempuan')->count() }} orang).
    </div>

    @include('pdf._ttd')

</body>
</html>