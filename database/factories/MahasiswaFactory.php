<?php

namespace Database\Factories;

use App\Models\Mahasiswa;
use App\Models\Prodi;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Data dummy mahasiswa dengan Faker locale id_ID.
 *
 * @extends Factory<Mahasiswa>
 */
class MahasiswaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $faker = fake('id_ID');
        $jenisKelamin = $faker->randomElement(Mahasiswa::JENIS_KELAMIN);
        $gender = $jenisKelamin === 'Laki-laki' ? 'male' : 'female';

        return [
            // Format NIM: 2024 (angkatan) + 120 + 2 digit urut, contoh 202412007.
            'nim' => $faker->unique()->numerify('2024120##'),
            'nama' => $faker->firstName($gender).' '.$faker->lastName($gender),
            'jenis_kelamin' => $jenisKelamin,
            'tanggal_lahir' => $faker->dateTimeBetween('-24 years', '-18 years')->format('Y-m-d'),
            'alamat' => $faker->streetAddress().', Bontang',
            'telepon' => $faker->numerify('08##########'),
            'email' => $faker->unique()->safeEmail(),
            'prodi_id' => Prodi::factory(),
            'angkatan' => 2024,
            'status' => $faker->randomElement(['Aktif', 'Aktif', 'Aktif', 'Aktif', 'Aktif', 'Aktif', 'Cuti', 'Lulus', 'Keluar']),
        ];
    }

    public function lakiLaki(): static
    {
        return $this->state(fn () => [
            'jenis_kelamin' => 'Laki-laki',
            'nama' => fake('id_ID')->firstNameMale().' '.fake('id_ID')->lastName('male'),
        ]);
    }

    public function perempuan(): static
    {
        return $this->state(fn () => [
            'jenis_kelamin' => 'Perempuan',
            'nama' => fake('id_ID')->firstNameFemale().' '.fake('id_ID')->lastName('female'),
        ]);
    }
}
