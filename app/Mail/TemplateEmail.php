<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;

class TemplateEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The email parameters.
     */
    protected array $emailParams;

    /**
     * Create a new message instance.
     *
     * @param array $emailParams
     */
    public function __construct(array $emailParams)
    {
        $this->emailParams = $emailParams;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $envelope = new Envelope(
            subject: $this->emailParams['subject'] ?? 'No Subject',
        );

        // Set sender
        if (!empty($this->emailParams['from'])) {
            $envelope->from(
                $this->emailParams['from'],
                $this->emailParams['from_name'] ?? config('mail.from.name')
            );
        }

        // Set reply-to
        if (!empty($this->emailParams['reply_to'])) {
            $envelope->replyTo($this->emailParams['reply_to']);
        }

        return $envelope;
    }

    /**
     * Get the message content.
     */
    public function content(): Content
    {
        $template = $this->emailParams['template'] ?? 'generic';
        $data = $this->emailParams['data'] ?? [];

        // Ensure data is always an array
        if (!is_array($data)) {
            $data = ['content' => $data];
        }

        // Build the view path
        $viewPath = "emails.templates.{$template}";

        // Check if template exists, fallback to generic
        if (!View::exists($viewPath)) {
            $viewPath = 'emails.templates.generic';
            Log::warning("Template '{$template}' not found, falling back to generic");
        }

        return new Content(
            view: $viewPath,
            with: array_merge(
                ['data' => $data, 'subject' => $this->emailParams['subject'] ?? 'No Subject'],
                is_array($data) ? $data : []
            ),
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array
     */
    public function attachments(): array
    {
        return [];
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(): self
    {
        Log::info('Building template email', [
            'to' => $this->emailParams['to'] ?? 'unknown',
            'template' => $this->emailParams['template'] ?? 'generic',
            'subject' => $this->emailParams['subject'] ?? 'No Subject',
        ]);

        return $this;
    }
}
