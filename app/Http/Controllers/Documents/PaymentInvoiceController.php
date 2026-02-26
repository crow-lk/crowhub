<?php

namespace App\Http\Controllers\Documents;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Contracts\View\View;

class PaymentInvoiceController extends Controller
{
    public function __invoke(Payment $payment): View
    {
        $payment->loadMissing(['lead', 'quote']);

        return view('documents.payment-invoice', [
            'payment' => $payment,
            'company' => config('company'),
        ]);
    }
}
