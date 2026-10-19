<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;

class DatabaseBackupMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $backupFile,
        public string $fileName
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Bizaa Database Backup - ' . now()->format('Y-m-d H:i')
        );
    }

    public function content(): Content
    {
        return new Content(
            text: 'emails.database-backup'
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromPath($this->backupFile)
                ->as($this->fileName)
                ->withMime('application/sql'),
        ];
    }
}
