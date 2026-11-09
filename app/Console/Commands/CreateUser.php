<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

#[Signature('user:create {email : Email akun} {--name= : Nama pengguna} {--role=operator : admin atau operator}')]
#[Description('Buat akun pengguna dengan password yang dimasukkan secara tersembunyi')]
class CreateUser extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (! $this->input->isInteractive()) {
            $this->error('Jalankan secara interaktif untuk memasukkan password akun dengan aman.');

            return self::INVALID;
        }

        $attributes = [
            'email' => $this->argument('email'),
            'name' => $this->option('name') ?: $this->ask('Nama pengguna'),
            'password' => $this->secret('Password akun'),
            'role' => $this->option('role'),
        ];
        $validator = Validator::make($attributes, [
            'email' => ['required', 'email', 'lowercase', 'max:255', 'unique:users,email'],
            'name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', Password::min(12)],
            'role' => ['required', Rule::in(array_keys(User::ROLES))],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $message) {
                $this->error($message);
            }

            return self::INVALID;
        }

        $user = new User($validator->safe()->except('role'));
        $user->forceFill(['role' => $attributes['role'], 'email_verified_at' => now()])->save();
        $this->info('Akun pengguna berhasil dibuat.');

        return self::SUCCESS;
    }
}
