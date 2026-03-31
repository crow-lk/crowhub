<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SendEmailRequest;
use App\Mail\TemplateEmail;
use App\Mail\CustomHtmlEmail;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class EmailController extends Controller
{
    /**
     * Send an email.
     *
     * External websites decide everything: sender, receiver, subject, template/html.
     * This system acts as a medium for email delivery.
     *
     * @param SendEmailRequest $request
     * @return JsonResponse
     */
    public function send(SendEmailRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            // Get sender from frontend - this is required
            $from = $validated['from'] ?? null;
            $fromName = $validated['from_name'] ?? config('mail.from.name');

            // Check if frontend sends their own HTML template
            $htmlContent = $validated['html'] ?? null;

            // Prepare email parameters
            $emailParams = [
                'to' => $validated['to'],
                'subject' => $validated['subject'] ?? 'No Subject',
                'from' => $from,
                'from_name' => $fromName,
                'reply_to' => $validated['reply_to'] ?? null,
                'cc' => $validated['cc'] ?? [],
                'bcc' => $validated['bcc'] ?? [],
            ];

            // If frontend provides HTML, use it directly
            if ($htmlContent) {
                $emailParams['html'] = $htmlContent;
                Mail::to($validated['to'])->send(new CustomHtmlEmail($emailParams));

                Log::info('Email sent with custom HTML', [
                    'to' => $validated['to'],
                    'from' => $from,
                    'subject' => $validated['subject'] ?? 'No Subject',
                ]);
            } else {
                // Use blade template system
                $template = $validated['template'] ?? 'generic';

                // Get data from frontend
                $data = $validated['data'] ?? $validated['body'] ?? [];
                if (is_string($data)) {
                    $data = ['content' => $data];
                }

                $emailParams['template'] = $template;
                $emailParams['data'] = $data;

                Mail::to($validated['to'])->send(new TemplateEmail($emailParams));

                Log::info('Email sent via template', [
                    'to' => $validated['to'],
                    'template' => $template,
                    'subject' => $validated['subject'] ?? 'No Subject',
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Email sent successfully',
                'data' => [
                    'to' => $validated['to'],
                    'from' => $from,
                    'subject' => $validated['subject'] ?? 'No Subject',
                    'sent_at' => now()->toISOString(),
                ],
            ], 200);
        } catch (\Exception $e) {
            Log::error('Failed to send email', [
                'error' => $e->getMessage(),
                'to' => $validated['to'] ?? 'unknown',
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send email',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Send multiple emails in batch.
     *
     * @param SendEmailRequest $request
     * @return JsonResponse
     */
    public function sendBatch(SendEmailRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $emails = $validated['emails'] ?? [];
            $results = [];

            foreach ($emails as $email) {
                try {
                    $from = $email['from'] ?? null;
                    $fromName = $email['from_name'] ?? config('mail.from.name');
                    $htmlContent = $email['html'] ?? null;

                    $emailParams = [
                        'to' => $email['to'],
                        'subject' => $email['subject'] ?? 'No Subject',
                        'from' => $from,
                        'from_name' => $fromName,
                        'reply_to' => $email['reply_to'] ?? null,
                    ];

                    if ($htmlContent) {
                        $emailParams['html'] = $htmlContent;
                        Mail::to($email['to'])->send(new CustomHtmlEmail($emailParams));
                    } else {
                        $template = $email['template'] ?? 'generic';
                        $data = $email['data'] ?? $email['body'] ?? [];
                        if (is_string($data)) {
                            $data = ['content' => $data];
                        }

                        $emailParams['template'] = $template;
                        $emailParams['data'] = $data;

                        Mail::to($email['to'])->send(new TemplateEmail($emailParams));
                    }

                    $results[] = [
                        'to' => $email['to'],
                        'from' => $from,
                        'status' => 'success',
                    ];
                } catch (\Exception $e) {
                    $results[] = [
                        'to' => $email['to'],
                        'from' => $email['from'] ?? 'unknown',
                        'status' => 'failed',
                        'error' => $e->getMessage(),
                    ];
                }
            }

            $successCount = count(array_filter($results, fn($r) => $r['status'] === 'success'));
            $failedCount = count($results) - $successCount;

            return response()->json([
                'success' => true,
                'message' => "Batch email sending completed: {$successCount} succeeded, {$failedCount} failed",
                'data' => [
                    'total' => count($results),
                    'succeeded' => $successCount,
                    'failed' => $failedCount,
                    'results' => $results,
                ],
            ], 200);
        } catch (\Exception $e) {
            Log::error('Failed to send batch emails', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send batch emails',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Test email configuration.
     *
     * @return JsonResponse
     */
    public function test(): JsonResponse
    {
        try {
            $mailDriver = config('mail.default');
            $mailHost = config('mail.mailers.smtp.host');
            $mailPort = config('mail.mailers.smtp.port');

            return response()->json([
                'success' => true,
                'message' => 'Email configuration is working',
                'data' => [
                    'driver' => $mailDriver,
                    'host' => $mailHost,
                    'port' => $mailPort,
                    'from_address' => config('mail.from.address'),
                    'from_name' => config('mail.from.name'),
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Email configuration check failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * List available templates.
     *
     * @return JsonResponse
     */
    public function listTemplates(): JsonResponse
    {
        $templates = [
            'generic' => 'Generic email template',
            'welcome' => 'Welcome email template',
            'order_confirmation' => 'Order confirmation template',
            'password_reset' => 'Password reset template',
            'notification' => 'Notification template',
        ];

        return response()->json([
            'success' => true,
            'message' => 'Available templates',
            'data' => [
                'templates' => $templates,
            ],
        ], 200);
    }
}
