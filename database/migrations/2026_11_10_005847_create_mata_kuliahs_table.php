<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mata_kuliahs', function (Blueprint $table): void {
            $table->id();
            $table->string('kode', 20)->unique();
            $table->string('nama');
            $table->unsignedTinyInteger('sks');
            $table->unsignedTinyInteger('semester');
            $table->foreignId('prodi_id')->constrained('prodis')->restrictOnDelete();
            $table->index('prodi_id');
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE public.mata_kuliahs ENABLE ROW LEVEL SECURITY');
            DB::statement('REVOKE ALL ON TABLE public.mata_kuliahs FROM anon, authenticated');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('mata_kuliahs');
    }
};
