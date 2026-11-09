<?php

namespace Database\Seeders;

use App\Models\MataKuliah;
use App\Models\Prodi;
use Illuminate\Database\Seeder;
use LogicException;

class MataKuliahSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local', 'testing')) {
            throw new LogicException('Data contoh hanya boleh dibuat pada lingkungan lokal atau testing.');
        }

        foreach (Prodi::query()->get() as $prodi) {
            MataKuliah::factory()->count(3)->for($prodi)->create();
        }
    }
}
