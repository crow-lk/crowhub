<?php

namespace App\Http\Controllers\Documents;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoicePrintController extends Controller
{
    public function __invoke(Invoice $invoice)
    {
        ini_set('memory_limit', '512M');

        $invoice->loadMissing([
            'job',
            'client.lead',
            'lead',
            'items.source',
            'payments',
        ]);

        $data = [
            'invoice' => $invoice,
            'company' => config('company'),
        ];

        if (! class_exists(Pdf::class)) {
            return response()->view('documents.project-invoice', $data);
        }

        $pdf = Pdf::loadView('documents.project-invoice', $data);
        $pdf->setPaper('a4', 'portrait');

        return response()->make(
            $pdf->output(),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="invoice_'.$invoice->invoice_no.'.pdf"',
            ]
        );
    }
}
