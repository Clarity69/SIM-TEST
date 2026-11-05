<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Index untuk foreign key deleted_by (temuan Supabase advisor unindexed_foreign_keys):
     * dipakai saat akun dihapus (ON DELETE SET NULL) agar tidak memindai seluruh tabel.
     */
    public function up(): void
    {
        Schema::table('mahasiswas', function (Blueprint $table) {
            $table->index('deleted_by');
        });
    }

    public function down(): void
    {
        Schema::table('mahasiswas', function (Blueprint $table) {
            $table->dropIndex(['deleted_by']);
        });
    }
};
