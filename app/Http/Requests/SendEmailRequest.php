<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class SendEmailRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Allow all requests - implement your own authentication logic here
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * Frontend decides: to, from, from_name, subject, template, data, body, cc, bcc, reply_to
     */
    public function rules(): array
    {
        return [
            // Receiver - required, decided by frontend
            'to' => 'required|email',

            // Sender - optional, decided by frontend
            'from' => 'nullable|email',
            'from_name' => 'nullable|string|max:100',

            // Subject - optional, decided by frontend
            'subject' => 'nullable|string|max:255',

            // Template - optional, decided by frontend
            'template' => 'nullable|string|max:100',

            // Data for template - optional, decided by frontend
            // Support both 'data' (object), 'body' (string), and 'html' (full HTML) fields
            'data' => 'nullable|array',
            'body' => 'nullable|string',
            'html' => 'nullable|string',

            // Optional recipients
            'cc' => 'nullable|array',
            'cc.*' => 'email',
            'bcc' => 'nullable|array',
            'bcc.*' => 'email',
            'reply_to' => 'nullable|email',

            // Batch email validation
            'emails' => 'nullable|array',
            'emails.*.to' => 'required_with:emails|email',
            'emails.*.from' => 'nullable|email',
            'emails.*.from_name' => 'nullable|string|max:100',
            'emails.*.subject' => 'nullable|string|max:255',
            'emails.*.template' => 'nullable|string|max:100',
            'emails.*.data' => 'nullable|array',
            'emails.*.body' => 'nullable|string',
            'emails.*.html' => 'nullable|string',
            'emails.*.cc' => 'nullable|array',
            'emails.*.bcc' => 'nullable|array',
            'emails.*.reply_to' => 'nullable|email',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'to.required' => 'The recipient email address is required.',
            'to.email' => 'Please provide a valid email address.',
            'from.email' => 'The sender email must be a valid email address.',
            'cc.*.email' => 'Each CC recipient must be a valid email address.',
            'bcc.*.email' => 'Each BCC recipient must be a valid email address.',
            'reply_to.email' => 'The reply-to email must be a valid email address.',
            'emails.*.to.required_with' => 'Each email in the batch must have a recipient.',
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $validator->errors(),
        ], 422));
    }
}
