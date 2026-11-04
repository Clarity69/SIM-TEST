<?php

namespace App\Listeners;

use App\Mail\WelcomeMail;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Mail;

/**
 * Kirim email selamat datang (lewat queue) setelah user mendaftar.
 */
class SendWelcomeEmail
{
    public function handle(Registered $event): void
    {
        if (! $event->user instanceof User) {
            return;
        }

        Mail::to($event->user)->queue(new WelcomeMail($event->user));
    }
}
