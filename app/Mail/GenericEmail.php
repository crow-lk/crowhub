<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenericEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The email data.
     */
    protected array $emailData;

    /**
     * Create a new message instance.
     *
     * @param array $emailData
     */
    public function __construct(array $emailData)
    {
        $this->emailData = $emailData;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $envelope = new Envelope(
            subject: $this->emailData['subject'],
        );

        // Set sender
        if (!empty($this->emailData['from'])) {
            $envelope->from(
                $this->emailData['from'],
                $this->emailData['fromName'] ?? config('mail.from.name')
            );
        }

        // Set reply-to
        if (!empty($this->emailData['replyTo'])) {
            $envelope->replyTo($this->emailData['replyTo']);
        }

        return $envelope;
    }

    /**
     * Get the message content.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.generic',
            with: [
                'body' => $this->emailData['body'],
                'subject' => $this->emailData['subject'],
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array
     */
    public function attachments(): array
    {
        $attachments = [];

        if (!empty($this->emailData['attachments'])) {
            foreach ($this->emailData['attachments'] as $attachment) {
                if (!empty($attachment['content'])) {
                    $attachments[] = new \Illuminate\Mail\Mailables\Attachment(
                        base64_decode($attachment['content']),
                        [
                            'as' => $attachment['name'] ?? 'attachment',
                            'mime' => $attachment['mime'] ?? 'application/octet-stream',
                        ]
                    );
                }
            }
        }

        return $attachments;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(): self
    {
        Log::info('Building email', [
            'to' => $this->emailData['to'],
            'subject' => $this->emailData['subject'],
        ]);

        return $this;
    }
}
