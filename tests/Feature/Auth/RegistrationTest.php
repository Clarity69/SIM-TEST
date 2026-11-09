<?php

namespace Tests\Feature\Auth;

use App\Mail\WelcomeMail;
use App\Notifications\QueuedVerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['auth.registration_enabled' => true]);
    }

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_registration_queues_welcome_mail_and_verification_email(): void
    {
        Mail::fake();
        Notification::fake();

        $this->post('/register', [
            'name' => 'Siti Rahmawati',
            'email' => 'siti@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $user = auth()->user();
        Mail::assertQueued(WelcomeMail::class, fn (WelcomeMail $mail) => $mail->hasTo('siti@example.com'));
        Notification::assertSentTo($user, QueuedVerifyEmail::class);
    }
}
