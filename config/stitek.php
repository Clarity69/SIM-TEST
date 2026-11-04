<?php

/*
|--------------------------------------------------------------------------
| Identitas STITEK Bontang
|--------------------------------------------------------------------------
|
| Satu sumber data kampus untuk kop surat PDF, footer email, dan footer
| aplikasi. Data kop diambil dari kop resmi di repo SIM-MAHASISWA; kontak
| humas dari https://pmb.stitek.ac.id/. Nama pejabat hanya diisi dari sumber
| asli; kaprodi per program studi disimpan di tabel prodis.
|
*/

return [

    'nama' => 'Sekolah Tinggi Teknologi Bontang',
    'singkatan' => 'STITEK Bontang',
    'logo' => 'images/logo-stitek.png',

    // Logo di email harus URL publik (Gmail memblokir localhost dan data URI).
    'logo_url' => env('MAIL_LOGO_URL', 'https://assets.siakadcloud.com/uploads/stitekbontang/logoaplikasi/1408.jpg'),

    'kop' => [
        'yayasan' => 'Yayasan Pendidikan Bessai Berinta',
        'instansi' => 'Sekolah Tinggi Teknologi Bontang',
        'fakultas' => 'Fakultas Teknik',
        'alamat' => 'Jalan Letjen S Parman No. 65 Bontang Barat, Kota Bontang, Kalimantan Timur, Kode Pos 75313',
        'kontak' => 'Telp. (0548) 28782 · Email: admin@stitek.ac.id · Website: https://stitek.ac.id/',
        'kota' => 'Bontang',
    ],

    'kontak' => [
        'alamat' => 'Jl. Letjen S. Parman No. 65, Bontang Barat, Bontang, Kalimantan Timur',
        'telepon' => '0811-5302-001',
        'email' => 'humas.stitek@gmail.com',
        'instagram' => '@stitekbontang',
        'facebook' => 'STITEKBTG',
        'website' => 'https://stitek.ac.id/',
    ],

    // Penanda tangan laporan "Semua Program Studi" (sumber: halaman Sambutan Ketua stitek.ac.id).
    'ketua' => [
        'nama' => 'Zaini, S.Pd., M.Pd.',
        'jabatan' => 'Ketua STITEK Bontang',
        'nidn' => null,
    ],

];
