<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * - angkatan: tahun masuk (diisi dari 4 digit awal NIM untuk data lama)
     * - status: Aktif / Cuti / Lulus / Keluar
     * - foto: path di storage/app/public (null = avatar inisial)
     * - deleted_by: siapa yang memindahkan data ke Sampah
     */
    public function up(): void
    {
        Schema::table('mahasiswas', function (Blueprint $table) {
            $table->unsignedSmallInteger('angkatan')->nullable()->after('prodi_id');
            $table->string('status', 10)->default('Aktif')->after('angkatan');
            $table->string('foto')->nullable()->after('email');
            $table->foreignId('deleted_by')->nullable()->after('deleted_at')->constrained('users')->nullOnDelete();

            $table->index('status');
            $table->index('angkatan');
        });

        // SQLite membangun ulang tabel saat menambah foreign key dan kehilangan klausa WHERE
        // pada unique index parsial NIM. Tegakkan kembali (aman juga di PostgreSQL).
        DB::statement('DROP INDEX IF EXISTS mahasiswas_nim_unique');
        DB::statement('CREATE UNIQUE INDEX mahasiswas_nim_unique ON mahasiswas (nim) WHERE deleted_at IS NULL');

        // Isi angkatan data lama dari awal NIM (contoh 202412001 -> 2024) bila masuk akal.
        DB::table('mahasiswas')
            ->whereNull('angkatan')
            ->orderBy('id')
            ->select('id', 'nim')
            ->chunkById(500, function ($rows) {
                foreach ($rows as $row) {
                    $tahun = (int) substr((string) $row->nim, 0, 4);

                    if ($tahun >= 2000 && $tahun <= (int) date('Y') + 1) {
                        DB::table('mahasiswas')->where('id', $row->id)->update(['angkatan' => $tahun]);
                    }
                }
            });
    }

    public function down(): void
    {
        Schema::table('mahasiswas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('deleted_by');
            $table->dropIndex(['status']);
            $table->dropIndex(['angkatan']);
            $table->dropColumn(['angkatan', 'status', 'foto']);
        });
    }
};
