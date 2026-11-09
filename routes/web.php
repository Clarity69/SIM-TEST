<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DosenController;
use App\Http\Controllers\KartuMahasiswaController;
use App\Http\Controllers\MahasiswaController;
use App\Http\Controllers\MahasiswaExportController;
use App\Http\Controllers\MahasiswaImportController;
use App\Http\Controllers\MataKuliahController;
use App\Http\Controllers\ProdiController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\TrashController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\EnsureAcademicTables;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Redirect halaman utama ke Dashboard
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Verifikasi kartu mahasiswa dari QR: publik, tetapi URL wajib bertanda tangan (signed).
Route::get('/kartu/verifikasi/{mahasiswa}', [KartuMahasiswaController::class, 'verifikasi'])
    ->whereNumber('mahasiswa')
    ->middleware(['signed', 'throttle:30,1'])
    ->name('kartu.verifikasi');

// Halaman data: wajib login dan email sudah diverifikasi
Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Pencarian global palet perintah (Ctrl+K)
    Route::get('/cari', SearchController::class)->middleware('throttle:120,1')->name('search');

    // Cetak & kirim PDF (wajib diletakkan SEBELUM resource route)
    Route::get('/mahasiswa/pdf', [MahasiswaController::class, 'pdf'])->name('mahasiswa.pdf');
    Route::post('/mahasiswa/pdf/email', [MahasiswaController::class, 'emailPdf'])
        ->middleware('throttle:5,1')
        ->name('mahasiswa.pdf.email');
    Route::get('/mahasiswa/{mahasiswa}/pdf', [MahasiswaController::class, 'pdfShow'])->name('mahasiswa.pdf.show');
    Route::get('/mahasiswa/{mahasiswa}/kartu', [KartuMahasiswaController::class, 'show'])->name('mahasiswa.kartu');
    Route::post('/mahasiswa/{mahasiswa}/pdf/email', [MahasiswaController::class, 'emailPdfShow'])
        ->middleware('throttle:5,1')
        ->name('mahasiswa.pdf.show.email');

    // Aksi massal & impor/ekspor Excel/CSV (sebelum resource route)
    Route::post('/mahasiswa/aksi-massal', [MahasiswaController::class, 'bulk'])->name('mahasiswa.bulk');
    Route::get('/mahasiswa/export', MahasiswaExportController::class)->name('mahasiswa.export');
    Route::get('/mahasiswa/import', [MahasiswaImportController::class, 'create'])->name('mahasiswa.import');
    Route::get('/mahasiswa/import/template', [MahasiswaImportController::class, 'template'])->name('mahasiswa.import.template');
    Route::post('/mahasiswa/import', [MahasiswaImportController::class, 'upload'])->middleware('throttle:10,1')->name('mahasiswa.import.upload');
    Route::get('/mahasiswa/import/{token}', [MahasiswaImportController::class, 'preview'])->whereUuid('token')->name('mahasiswa.import.preview');
    Route::post('/mahasiswa/import/{token}', [MahasiswaImportController::class, 'store'])->whereUuid('token')->name('mahasiswa.import.store');
    Route::get('/mahasiswa/import/{token}/laporan', [MahasiswaImportController::class, 'report'])->whereUuid('token')->name('mahasiswa.import.report');

    // Resource CRUD Mahasiswa & Prodi
    Route::resource('mahasiswa', MahasiswaController::class);
    Route::resource('prodi', ProdiController::class);
    Route::resource('dosen', DosenController::class)->only('index');
    Route::resource('mata-kuliah', MataKuliahController::class)->only('index');
    Route::middleware(EnsureAcademicTables::class)->group(function (): void {
        Route::resource('dosen', DosenController::class)->except(['index', 'show']);
        Route::resource('mata-kuliah', MataKuliahController::class)->except(['index', 'show']);
    });

    // Khusus admin
    Route::middleware('can:admin')->group(function () {
        Route::get('/pengguna', [UserController::class, 'index'])->name('users.index');
        Route::get('/pengguna/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/pengguna', [UserController::class, 'store'])->name('users.store');
        Route::patch('/pengguna/{user}/role', [UserController::class, 'updateRole'])->name('users.role');
        Route::delete('/pengguna/{user}', [UserController::class, 'destroy'])->name('users.destroy');

        Route::get('/aktivitas', [ActivityLogController::class, 'index'])->name('activity.index');

        Route::get('/sampah', [TrashController::class, 'index'])->name('trash.index');
        Route::post('/sampah/aksi', [TrashController::class, 'bulk'])->name('trash.bulk');
        Route::delete('/sampah', [TrashController::class, 'empty'])->name('trash.empty');
        Route::patch('/sampah/{mahasiswa}/pulihkan', [TrashController::class, 'restore'])->name('trash.restore')->withTrashed();
        Route::delete('/sampah/{mahasiswa}', [TrashController::class, 'destroy'])->name('trash.destroy')->withTrashed();
    });
});

// Profil: cukup login (user yang belum verifikasi tetap bisa memperbaiki email)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::patch('/profile/notifications', [ProfileController::class, 'updateNotifications'])->name('profile.notifications.update');
});

// Route Autentikasi dari Laravel Breeze (Login, Register, Logout, Reset Password, Verifikasi)
require __DIR__.'/auth.php';
