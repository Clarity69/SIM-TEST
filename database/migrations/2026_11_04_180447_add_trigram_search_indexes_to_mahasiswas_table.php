<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Index GIN trigram agar pencarian `ILIKE '%kata%'` pada nama dan NIM tidak
     * melakukan sequential scan. Hanya untuk PostgreSQL (Supabase); SQLite saat test dilewati.
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('CREATE SCHEMA IF NOT EXISTS extensions');
        DB::statement('CREATE EXTENSION IF NOT EXISTS pg_trgm WITH SCHEMA extensions');

        $schema = DB::scalar("SELECT extnamespace::regnamespace::text FROM pg_extension WHERE extname = 'pg_trgm'");

        DB::statement("CREATE INDEX IF NOT EXISTS mahasiswas_nama_trgm_index ON mahasiswas USING gin (nama {$schema}.gin_trgm_ops)");
        DB::statement("CREATE INDEX IF NOT EXISTS mahasiswas_nim_trgm_index ON mahasiswas USING gin (nim {$schema}.gin_trgm_ops)");
    }

    /**
     * Extension pg_trgm sengaja tidak di-drop karena bisa dipakai objek lain.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('DROP INDEX IF EXISTS mahasiswas_nama_trgm_index');
        DB::statement('DROP INDEX IF EXISTS mahasiswas_nim_trgm_index');
    }
};
