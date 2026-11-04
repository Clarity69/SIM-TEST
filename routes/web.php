<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MahasiswaController;
use App\Http\Controllers\ProdiController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
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

// Halaman data: wajib login dan email sudah diverifikasi
Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Cetak & kirim PDF (wajib diletakkan SEBELUM resource route)
    Route::get('/mahasiswa/pdf', [MahasiswaController::class, 'pdf'])->name('mahasiswa.pdf');
    Route::post('/mahasiswa/pdf/email', [MahasiswaController::class, 'emailPdf'])
        ->middleware('throttle:5,1')
        ->name('mahasiswa.pdf.email');
    Route::get('/mahasiswa/{mahasiswa}/pdf', [MahasiswaController::class, 'pdfShow'])->name('mahasiswa.pdf.show');
    Route::post('/mahasiswa/{mahasiswa}/pdf/email', [MahasiswaController::class, 'emailPdfShow'])
        ->middleware('throttle:5,1')
        ->name('mahasiswa.pdf.show.email');

    // Resource CRUD Mahasiswa & Prodi
    Route::resource('mahasiswa', MahasiswaController::class);
    Route::resource('prodi', ProdiController::class);

    // Khusus admin
    Route::middleware('can:admin')->group(function () {
        Route::get('/pengguna', [UserController::class, 'index'])->name('users.index');
        Route::patch('/pengguna/{user}/role', [UserController::class, 'updateRole'])->name('users.role');
        Route::delete('/pengguna/{user}', [UserController::class, 'destroy'])->name('users.destroy');
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
