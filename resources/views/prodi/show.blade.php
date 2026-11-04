@extends('layouts.app')

@section('title', 'Detail Program Studi')
@section('page-title', 'Detail Program Studi')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-1">Detail Program Studi</h2>
        <p class="text-muted mb-0">Informasi detail program studi dan daftar mahasiswa terdaftar.</p>
    </div>
    <a href="{{ route('prodi.index') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left me-1"></i> Kembali
    </a>
</div>

<div class="row g-4">
    {{-- INFORMASI PRODI --}}
    <div class="col-md-4">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body p-4">
                <div class="bg-primary bg-opacity-10 text-primary rounded-circle mx-auto d-flex align-items-center justify-content-center mb-3" style="width:80px; height:80px;">
                    <i class="bi bi-building fs-1"></i>
                </div>
                <h4 class="fw-bold mb-1">{{ $prodi->nama_prodi }}</h4>
                <span class="badge bg-primary px-3 py-2 rounded-pill mb-3">{{ $prodi->kode_prodi }}</span>
                <hr>
                <div class="text-start">
                    <p class="mb-2">
                        <strong class="text-muted d-block small">Fakultas:</strong>
                        <span class="fw-semibold text-dark">{{ $prodi->fakultas ?? '-' }}</span>
                    </p>
                    <p class="mb-0">
                        <strong class="text-muted d-block small">Jumlah Mahasiswa:</strong>
                        <span class="badge bg-info bg-opacity-10 text-info px-3 py-2 rounded-pill fw-bold">
                            {{ $mahasiswas->total() }} Mahasiswa
                        </span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- DAFTAR MAHASISWA --}}
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="fw-bold mb-0">Daftar Mahasiswa Terdaftar</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="px-4">NIM</th>
                                <th>Nama</th>
                                <th>Jenis Kelamin</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($mahasiswas as $mahasiswa)
                                <tr>
                                    <td class="px-4 fw-semibold">{{ $mahasiswa->nim }}</td>
                                    <td class="fw-bold">{{ $mahasiswa->nama }}</td>
                                    <td>
                                        @if($mahasiswa->jenis_kelamin === 'Laki-laki')
                                            <span class="badge bg-info bg-opacity-10 text-info rounded-pill">
                                                <i class="bi bi-person-fill me-1"></i>Laki-laki
                                            </span>
                                        @else
                                            <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill">
                                                <i class="bi bi-person-heart me-1"></i>Perempuan
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-4">
                                        <i class="bi bi-inbox fs-2 d-block mb-2 text-secondary"></i>
                                        Belum ada mahasiswa pada program studi ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($mahasiswas->hasPages())
                    <div class="px-4 py-3">{{ $mahasiswas->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection