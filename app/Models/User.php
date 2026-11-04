<?php

namespace App\Models;

use App\Auth\CachedUserProvider;
use App\Notifications\QueuedResetPassword;
use App\Notifications\QueuedVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;

#[Fillable(['name', 'email', 'password', 'notify_mahasiswa_changes'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_ADMIN = 'admin';

    public const ROLE_OPERATOR = 'operator';

    /** @var array<string, string> */
    public const ROLES = [
        self::ROLE_ADMIN => 'Admin',
        self::ROLE_OPERATOR => 'Operator',
    ];

    /**
     * Nilai default sama dengan migration agar model baru langsung punya role.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'role' => self::ROLE_OPERATOR,
    ];

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /**
     * Data user yang login di-cache (CachedUserProvider); bersihkan saat berubah.
     */
    protected static function booted(): void
    {
        $forget = fn (User $user) => Cache::forget(CachedUserProvider::cacheKey($user->getKey()));

        static::saved($forget);
        static::deleted($forget);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'notify_mahasiswa_changes' => 'boolean',
        ];
    }

    /**
     * Email verifikasi dikirim lewat queue (lihat QueuedVerifyEmail).
     */
    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new QueuedVerifyEmail);
    }

    /**
     * Email reset password dikirim lewat queue (lihat QueuedResetPassword).
     *
     * @param  string  $token
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new QueuedResetPassword($token));
    }
}
