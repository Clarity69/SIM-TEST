<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Index untuk pola query halaman Data Mahasiswa dan Dashboard.
     *
     * - (prodi_id, jenis_kelamin): filter prodi saja maupun prodi + JK. Kolom prodi_id
     *   berada di depan, sehingga index tunggal prodi_id tidak diperlukan lagi.
     * - jenis_kelamin: filter JK tanpa prodi.
     * - nama: sorting kolom nama.
     * - created_at: urutan default latest() dan statistik 30 hari.
     * nim sudah unik (index unik dari migration awal).
     */
    public function up(): void
    {
        Schema::table('mahasiswas', function (Blueprint $table) {
            $table->index(['prodi_id', 'jenis_kelamin']);
            $table->index('jenis_kelamin');
            $table->index('nama');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::table('mahasiswas', function (Blueprint $table) {
            $table->dropIndex(['prodi_id', 'jenis_kelamin']);
            $table->dropIndex(['jenis_kelamin']);
            $table->dropIndex(['nama']);
            $table->dropIndex(['created_at']);
        });
    }
};
