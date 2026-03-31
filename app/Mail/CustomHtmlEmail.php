<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CustomHtmlEmail extends Mailable
{
    use Queueable, SerializesModels;

    protected array $emailParams;

    public function __construct(array $emailParams)
    {
        $this->emailParams = $emailParams;
    }

    public function envelope(): Envelope
    {
        $envelope = new Envelope(
            subject: $this->emailParams['subject'] ?? 'No Subject',
        );

        if (!empty($this->emailParams['from'])) {
            $envelope->from(
                $this->emailParams['from'],
                $this->emailParams['from_name'] ?? config('mail.from.name')
            );
        }

        if (!empty($this->emailParams['reply_to'])) {
            $envelope->replyTo($this->emailParams['reply_to']);
        }

        return $envelope;
    }

    public function content(): Content
    {
        $html = $this->emailParams['html'] ?? $this->emailParams['body'] ?? '<p>No content provided.</p>';

        return new Content(
            view: 'emails.raw',
            with: ['htmlContent' => $html],
        );
    }

    public function attachments(): array
    {
        return [];
    }

    public function build(): self
    {
        Log::info('Building custom HTML email', [
            'to' => $this->emailParams['to'] ?? 'unknown',
            'from' => $this->emailParams['from'] ?? 'unknown',
            'subject' => $this->emailParams['subject'] ?? 'No Subject',
        ]);

        return $this;
    }
}
