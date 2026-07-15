<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DailyReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $date;
    public array $stats;

    public function __construct(string $date, array $stats, public string $pdfPath)
    {
        $this->date = $date;
        $this->stats = $stats;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Reporte Diario - {$this->date}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.daily-report',
            with: [
                'date' => $this->date,
                'stats' => $this->stats,
            ],
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromStorageDisk('local', $this->pdfPath),
        ];
    }
}
