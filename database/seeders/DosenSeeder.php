<?php

namespace Database\Seeders;

use App\Models\Dosen;
use App\Models\Prodi;
use Illuminate\Database\Seeder;
use LogicException;

class DosenSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local', 'testing')) {
            throw new LogicException('Data contoh hanya boleh dibuat pada lingkungan lokal atau testing.');
        }

        foreach (Prodi::query()->get() as $prodi) {
            Dosen::factory()->count(3)->for($prodi)->create();
        }
    }
}
