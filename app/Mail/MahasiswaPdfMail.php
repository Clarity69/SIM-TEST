<?php

namespace App\Mail;

use App\Models\Mahasiswa;
use App\Services\MahasiswaPdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Laporan PDF mahasiswa sebagai lampiran email. PDF dibuat di worker queue
 * (bukan di request) supaya halaman tidak menunggu proses render dompdf.
 */
class MahasiswaPdfMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * @param  array<string, mixed>  $filters  filter daftar (diabaikan bila $mahasiswa diisi)
     */
    public function __construct(
        public string $pengirim,
        public array $filters = [],
        public ?Mahasiswa $mahasiswa = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->mahasiswa
            ? "Biodata mahasiswa {$this->mahasiswa->nama} ({$this->mahasiswa->nim})"
            : 'Laporan data mahasiswa — SIM Mahasiswa STITEK');
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.mahasiswa-pdf', with: [
            'pengirim' => $this->pengirim,
            'mahasiswa' => $this->mahasiswa,
            'filters' => $this->filters,
        ]);
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        $pdf = app(MahasiswaPdf::class);

        if ($this->mahasiswa) {
            return [
                Attachment::fromData(fn () => $pdf->detail($this->mahasiswa)->output(), $pdf->namaFileDetail($this->mahasiswa))
                    ->withMime('application/pdf'),
            ];
        }

        return [
            Attachment::fromData(fn () => $pdf->daftar($this->filters)->output(), $pdf->namaFileDaftar($this->filters))
                ->withMime('application/pdf'),
        ];
    }
}
