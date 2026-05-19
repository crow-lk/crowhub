<?php

namespace App\Http\Controllers\Documents;

use App\Http\Controllers\Controller;
use App\Models\ProjectInvoice;
use Barryvdh\DomPDF\Facade\Pdf;

class ProjectInvoiceController extends Controller
{
    public function __invoke(ProjectInvoice $invoice)
    {
        ini_set('memory_limit', '512M');

        $invoice->loadMissing([
            'project.client.lead',
            'items.task',
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
                'Content-Disposition' => 'inline; filename="project_invoice_'.$invoice->invoice_no.'.pdf"',
            ]
        );
    }
}
