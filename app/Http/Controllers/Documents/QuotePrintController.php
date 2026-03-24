<?php

namespace App\Http\Controllers\Documents;

use App\Http\Controllers\Controller;
use App\Models\Quote;
use Barryvdh\DomPDF\Facade\Pdf;

class QuotePrintController extends Controller
{
    public function __invoke(Quote $quote)
    {
        $quote->loadMissing(['lead', 'items', 'termsAndConditions', 'termsAndConditions.parent']);

        // Generate PDF
        $pdf = Pdf::loadView('documents.quotation', [
            'quote' => $quote,
            'company' => config('company'),
        ]);

        // Set paper size and orientation
        $pdf->setPaper('a4', 'portrait');

        // Return the PDF as inline (view in browser)
        return response()->make(
            $pdf->output(),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="quotation_' . $quote->quote_no . '.pdf"',
            ]
        );
    }
}
