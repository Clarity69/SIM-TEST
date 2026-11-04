<?php

namespace App\Console\Commands;

use App\Mail\TestMail;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Throwable;

#[Signature('mail:test {email : Alamat email tujuan}')]
#[Description('Kirim email uji untuk memeriksa konfigurasi SMTP (tanpa menampilkan kredensial)')]
class SendTestMail extends Command
{
    public function handle(): int
    {
        $email = (string) $this->argument('email');

        if (Validator::make(['email' => $email], ['email' => 'required|email'])->fails()) {
            $this->error("Alamat email tidak valid: {$email}");

            return self::INVALID;
        }

        $this->line(sprintf(
            'Mailer: %s · Host: %s:%s · From: %s',
            config('mail.default'),
            config('mail.mailers.smtp.host'),
            config('mail.mailers.smtp.port'),
            config('mail.from.address'),
        ));

        try {
            Mail::to($email)->send(new TestMail);
        } catch (Throwable $e) {
            $this->error('Gagal mengirim email: '.$this->sanitize($e->getMessage()));

            return self::FAILURE;
        }

        $this->info("Berhasil: email uji terkirim ke {$email}.");

        return self::SUCCESS;
    }

    /**
     * Pastikan username/password SMTP tidak ikut tercetak di pesan error.
     */
    private function sanitize(string $message): string
    {
        $secrets = array_filter([
            config('mail.mailers.smtp.password'),
            config('mail.mailers.smtp.username'),
        ]);

        return str_replace($secrets, '[disembunyikan]', $message);
    }
}
