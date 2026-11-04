<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Email reset password bawaan Laravel, dikirim lewat queue dan berbahasa Indonesia.
 */
class QueuedResetPassword extends ResetPassword implements ShouldQueue
{
    use Queueable;

    /**
     * @param  string  $url
     */
    protected function buildMailMessage($url): MailMessage
    {
        $expire = config('auth.passwords.'.config('auth.defaults.passwords').'.expire');

        return (new MailMessage)
            ->subject('Reset password — SIM Mahasiswa STITEK')
            ->greeting('Halo!')
            ->line('Kami menerima permintaan untuk mengatur ulang password akun Anda.')
            ->action('Atur Ulang Password', $url)
            ->line("Tautan ini berlaku selama {$expire} menit.")
            ->line('Jika Anda tidak meminta reset password, abaikan email ini.')
            ->salutation('Salam, Tim SIM Mahasiswa '.config('stitek.singkatan'));
    }
}
