<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\UserAdministration;
use DomainException;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('user:role {email : Email akun} {role : admin atau operator}')]
#[Description('Ubah role pengguna (admin/operator)')]
class SetUserRole extends Command
{
    public function handle(UserAdministration $users): int
    {
        $role = (string) $this->argument('role');

        if (! array_key_exists($role, User::ROLES)) {
            $this->error('Role harus salah satu dari: '.implode(', ', array_keys(User::ROLES)));

            return self::INVALID;
        }

        $user = User::where('email', $this->argument('email'))->first();

        if (! $user) {
            $this->error('Akun dengan email tersebut tidak ditemukan.');

            return self::FAILURE;
        }

        try {
            $users->changeRole($user, $role);
        } catch (DomainException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }
        $this->info("{$user->name} sekarang {$role}.");

        return self::SUCCESS;
    }
}
