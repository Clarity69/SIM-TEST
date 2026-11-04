<?php

namespace Database\Factories;

use App\Models\Prodi;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Untuk test saja; data prodi asli ada di ProdiSeeder.
 *
 * @extends Factory<Prodi>
 */
class ProdiFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'kode_prodi' => fake()->unique()->numerify('9####'),
            'singkatan' => strtoupper(fake()->unique()->lexify('??')),
            'nama_prodi' => 'Prodi '.fake()->unique()->word(),
            'fakultas' => null,
        ];
    }
}
