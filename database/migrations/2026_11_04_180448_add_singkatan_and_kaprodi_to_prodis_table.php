<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * - singkatan: label pendek (TI, SI, ...) untuk grafik dan badge, karena kode_prodi
     *   sekarang memakai kode PDDIKTI (55201, ...).
     * - kaprodi*: penandatangan laporan PDF. Nullable: diisi hanya dengan data asli.
     */
    public function up(): void
    {
        Schema::table('prodis', function (Blueprint $table) {
            $table->string('singkatan', 10)->nullable()->after('kode_prodi');
            $table->string('kaprodi')->nullable()->after('fakultas');
            $table->string('kaprodi_jabatan', 100)->default('Ketua Program Studi')->after('kaprodi');
            $table->string('kaprodi_nidn', 20)->nullable()->after('kaprodi_jabatan');
        });
    }

    public function down(): void
    {
        Schema::table('prodis', function (Blueprint $table) {
            $table->dropColumn(['singkatan', 'kaprodi', 'kaprodi_jabatan', 'kaprodi_nidn']);
        });
    }
};
