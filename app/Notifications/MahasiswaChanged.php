<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notifikasi ke admin saat data mahasiswa ditambah atau dihapus.
 * Data dikirim sebagai array (bukan model) karena mahasiswa yang dihapus
 * tidak bisa di-unserialize lagi oleh worker queue.
 */
class MahasiswaChanged extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param  'ditambahkan'|'dihapus'  $aksi
     * @param  array{id: int, nim: string, nama: string, jenis_kelamin: string, prodi: ?string}  $mahasiswa
     */
    public function __construct(
        public string $aksi,
        public array $mahasiswa,
        public string $oleh,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Mahasiswa {$this->aksi}: {$this->mahasiswa['nama']} ({$this->mahasiswa['nim']})")
            ->markdown('mail.mahasiswa-changed', [
                'aksi' => $this->aksi,
                'mahasiswa' => $this->mahasiswa,
                'oleh' => $this->oleh,
                'url' => $this->aksi === 'ditambahkan'
                    ? route('mahasiswa.show', $this->mahasiswa['id'])
                    : route('mahasiswa.index'),
                'pengaturanUrl' => route('profile.edit'),
            ]);
    }
}
