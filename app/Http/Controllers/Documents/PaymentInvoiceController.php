<?php

namespace App\Http\Controllers\Documents;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PaymentInvoiceController extends Controller
{
    public function __invoke(Payment $payment)
    {
        $payment->loadMissing(['lead', 'quote', 'termsAndConditions', 'termsAndConditions.parent']);

        // Generate PDF
        $pdf = Pdf::loadView('documents.payment-invoice', [
            'payment' => $payment,
            'company' => config('company'),
        ]);

        // Set paper size and orientation
        $pdf->setPaper('a4', 'portrait');

        // Generate invoice number
        $invoiceNo = 'INV-' . str_pad((string) $payment->id, 5, '0', STR_PAD_LEFT);

        // Return the PDF as inline (view in browser)
        return response()->make(
            $pdf->output(),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="invoice_' . $invoiceNo . '.pdf"',
            ]
        );
    }
}
