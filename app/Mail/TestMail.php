<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Email uji koneksi SMTP. Sengaja tidak di-queue agar error langsung terlihat di `mail:test`.
 */
class TestMail extends Mailable
{
    use Queueable, SerializesModels;

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Tes koneksi email — SIM Mahasiswa STITEK');
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.test', with: [
            'mailer' => config('mail.default'),
            'host' => config('mail.mailers.smtp.host'),
            'port' => config('mail.mailers.smtp.port'),
        ]);
    }
}
