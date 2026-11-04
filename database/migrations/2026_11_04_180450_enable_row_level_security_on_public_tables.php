<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Tabel di schema public Supabase otomatis terekspos lewat Data API (PostgREST).
     * Mengaktifkan RLS tanpa policy menutup akses role anon/authenticated, sementara
     * Laravel yang terhubung sebagai owner tabel (postgres) tetap bisa membaca/menulis.
     *
     * @var list<string>
     */
    private array $tables = [
        'users', 'password_reset_tokens', 'sessions',
        'cache', 'cache_locks',
        'jobs', 'job_batches', 'failed_jobs',
        'prodis', 'mahasiswas', 'migrations',
    ];

    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        foreach ($this->tables as $table) {
            DB::statement("ALTER TABLE IF EXISTS public.{$table} ENABLE ROW LEVEL SECURITY");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        foreach ($this->tables as $table) {
            DB::statement("ALTER TABLE IF EXISTS public.{$table} DISABLE ROW LEVEL SECURITY");
        }
    }
};
