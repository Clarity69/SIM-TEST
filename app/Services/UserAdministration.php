<?php

namespace App\Services;

use App\Models\User;
use Closure;
use DomainException;
use Illuminate\Support\Facades\DB;

class UserAdministration
{
    /** @param (Closure(): void)|null $beforeDelete */
    public function delete(User $user, ?Closure $beforeDelete = null): void
    {
        DB::transaction(function () use ($user, $beforeDelete) {
            $this->protectLastAdmin($user);
            if ($beforeDelete !== null) {
                $beforeDelete();
            }
            $user->delete();
        }, 3);
    }

    public function changeRole(User $user, string $role): void
    {
        DB::transaction(function () use ($user, $role) {
            $this->protectLastAdmin($user, $role);
            $user->forceFill(['role' => $role])->save();
        }, 3);
    }

    private function protectLastAdmin(User $user, ?string $newRole = null): void
    {
        $admins = User::query()->where('role', User::ROLE_ADMIN)->orderBy('id')->lockForUpdate()->get(['id']);
        $user->setRawAttributes(User::query()->whereKey($user->id)->lockForUpdate()->firstOrFail()->getAttributes(), true);

        if ($user->isAdmin() && $newRole !== User::ROLE_ADMIN && $admins->count() <= 1) {
            throw new DomainException('Admin terakhir tidak dapat dihapus atau diturunkan rolenya.');
        }
    }
}
