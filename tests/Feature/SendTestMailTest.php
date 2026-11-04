<?php

namespace Tests\Feature;

use App\Mail\TestMail;
use Illuminate\Support\Facades\Mail;
use RuntimeException;
use Tests\TestCase;

class SendTestMailTest extends TestCase
{
    public function test_sends_test_mail_and_reports_success(): void
    {
        Mail::fake();

        $this->artisan('mail:test', ['email' => 'tujuan@example.com'])
            ->expectsOutputToContain('Berhasil')
            ->assertSuccessful();

        Mail::assertSent(TestMail::class, fn (TestMail $mail) => $mail->hasTo('tujuan@example.com'));
    }

    public function test_rejects_invalid_address(): void
    {
        Mail::fake();

        $this->artisan('mail:test', ['email' => 'bukan-email'])->assertExitCode(2);

        Mail::assertNothingSent();
    }

    public function test_failure_message_hides_smtp_credentials(): void
    {
        config([
            'mail.mailers.smtp.username' => 'akun@gmail.com',
            'mail.mailers.smtp.password' => 'abcdefghijklmnop',
        ]);
        Mail::shouldReceive('to')->andReturnSelf();
        Mail::shouldReceive('send')->andThrow(new RuntimeException(
            'Failed to authenticate with username "akun@gmail.com" using password "abcdefghijklmnop".'
        ));

        $this->artisan('mail:test', ['email' => 'tujuan@example.com'])
            ->expectsOutputToContain('[disembunyikan]')
            ->doesntExpectOutputToContain('abcdefghijklmnop')
            ->assertFailed();
    }
}
