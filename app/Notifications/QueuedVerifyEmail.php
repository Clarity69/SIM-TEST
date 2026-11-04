<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Email verifikasi bawaan Laravel, dikirim lewat queue dan berbahasa Indonesia.
 */
class QueuedVerifyEmail extends VerifyEmail implements ShouldQueue
{
    use Queueable;

    /**
     * @param  string  $url
     */
    protected function buildMailMessage($url): MailMessage
    {
        return (new MailMessage)
            ->subject('Verifikasi alamat email Anda — SIM Mahasiswa STITEK')
            ->greeting('Halo!')
            ->line('Klik tombol di bawah untuk memverifikasi alamat email akun SIM Mahasiswa Anda.')
            ->action('Verifikasi Email', $url)
            ->line('Tautan ini berlaku selama '.config('auth.verification.expire', 60).' menit.')
            ->line('Jika Anda tidak membuat akun, abaikan email ini.')
            ->salutation('Salam, Tim SIM Mahasiswa '.config('stitek.singkatan'));
    }
}
