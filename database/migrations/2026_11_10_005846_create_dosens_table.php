<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dosens', function (Blueprint $table): void {
            $table->id();
            $table->string('nidn', 20)->nullable()->unique();
            $table->string('nama');
            $table->string('email')->nullable();
            $table->foreignId('prodi_id')->constrained('prodis')->restrictOnDelete();
            $table->index('prodi_id');
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE public.dosens ENABLE ROW LEVEL SECURITY');
            DB::statement('REVOKE ALL ON TABLE public.dosens FROM anon, authenticated');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('dosens');
    }
};
