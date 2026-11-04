<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Ringkasan perubahan massal (impor, hapus massal, ubah status massal):
 * satu email per aksi, bukan satu email per mahasiswa.
 */
class MahasiswaBulkChanged extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param  list<string>  $contoh  maksimal beberapa "Nama (NIM)" sebagai gambaran
     */
    public function __construct(
        public string $aksi,
        public int $jumlah,
        public array $contoh,
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
            ->subject("{$this->jumlah} mahasiswa {$this->aksi}")
            ->markdown('mail.mahasiswa-bulk-changed', [
                'aksi' => $this->aksi,
                'jumlah' => $this->jumlah,
                'contoh' => $this->contoh,
                'oleh' => $this->oleh,
                'url' => route('mahasiswa.index'),
                'pengaturanUrl' => route('profile.edit'),
            ]);
    }
}
