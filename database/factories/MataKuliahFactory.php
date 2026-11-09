<?php

namespace Database\Factories;

use App\Models\MataKuliah;
use App\Models\Prodi;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<MataKuliah> */
class MataKuliahFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'kode' => fake()->unique()->bothify('MK###??'),
            'nama' => fake()->words(3, true),
            'sks' => 3,
            'semester' => 1,
            'prodi_id' => Prodi::factory(),
            'aktif' => true,
        ];
    }

    public function nonaktif(): static
    {
        return $this->state(fn (): array => ['aktif' => false]);
    }
}
