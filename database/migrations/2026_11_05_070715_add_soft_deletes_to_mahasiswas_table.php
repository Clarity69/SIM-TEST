<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Soft delete mahasiswa:
     * - statistik dashboard bisa menghitung populasi 30 hari lalu dengan benar
     *   (data yang dihapus kemudian tetap terhitung di masa lalunya);
     * - dasar fitur Sampah (restore / hapus permanen).
     *
     * NIM tetap unik hanya untuk data yang belum dihapus (partial unique index,
     * didukung PostgreSQL dan SQLite), sehingga NIM data di Sampah bisa dipakai lagi.
     */
    public function up(): void
    {
        Schema::table('mahasiswas', function (Blueprint $table) {
            $table->softDeletes();
            $table->dropUnique(['nim']);
        });

        DB::statement('CREATE UNIQUE INDEX mahasiswas_nim_unique ON mahasiswas (nim) WHERE deleted_at IS NULL');
    }

    /**
     * Gagal bila ada NIM ganda di antara data yang sudah dihapus; kosongkan Sampah dulu.
     */
    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS mahasiswas_nim_unique');

        Schema::table('mahasiswas', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->unique('nim');
        });
    }
};
