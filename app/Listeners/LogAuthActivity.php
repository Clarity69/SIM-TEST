<?php

namespace App\Listeners;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\PasswordReset;

/**
 * Catat login, logout, dan reset password ke log aktivitas.
 */
class LogAuthActivity
{
    public function handle(Login|Logout|PasswordReset $event): void
    {
        if (! $event->user instanceof User) {
            return;
        }

        [$jenis, $deskripsi] = match (true) {
            $event instanceof Login => ['login', 'Login ke aplikasi'.($event->remember ? ' (ingat saya)' : '')],
            $event instanceof Logout => ['logout', 'Logout dari aplikasi'],
            default => ['account', 'Mereset password lewat email'],
        };

        ActivityLog::catat($jenis, $deskripsi, pelaku: $event->user);
    }
}
