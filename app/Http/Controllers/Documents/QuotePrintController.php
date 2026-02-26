<?php

namespace App\Http\Controllers\Documents;

use App\Http\Controllers\Controller;
use App\Models\Quote;
use Illuminate\Contracts\View\View;

class QuotePrintController extends Controller
{
    public function __invoke(Quote $quote): View
    {
        $quote->loadMissing(['lead', 'items']);

        return view('documents.quotation', [
            'quote' => $quote,
            'company' => config('company'),
        ]);
    }
}
