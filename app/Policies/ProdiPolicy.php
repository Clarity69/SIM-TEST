<?php

namespace App\Policies;

use App\Models\Prodi;
use App\Models\User;

/**
 * Operator boleh menambah/mengubah prodi; menghapus prodi khusus admin.
 */
class ProdiPolicy
{
    public function delete(User $user, Prodi $prodi): bool
    {
        return $user->isAdmin();
    }
}
