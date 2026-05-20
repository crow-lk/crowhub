<?php

namespace App\Observers;

use App\Models\ClientActivity;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\Billing\InvoiceStatusUpdater;
use App\Services\Sms\SmsAutomation;

class PaymentObserver
{
    public function __construct(protected SmsAutomation $automation) {}

    public function created(Payment $payment): void
    {
        if ($payment->invoice) {
            app(InvoiceStatusUpdater::class)->update($payment->invoice);
            ClientActivity::recordFor($payment, 'payment', 'Payment recorded for '.$payment->invoice->invoice_no, 'LKR '.number_format((float) $payment->amount, 2));
        }

        $this->automation->sendAdvanceOrProgressPayment($payment);
    }

    public function updated(Payment $payment): void
    {
        $this->refreshInvoiceStatus($payment);

        $originalInvoiceId = $payment->getOriginal('invoice_id');

        if ($originalInvoiceId && $originalInvoiceId !== $payment->invoice_id) {
            $invoice = Invoice::find($originalInvoiceId);

            if ($invoice) {
                app(InvoiceStatusUpdater::class)->update($invoice);
            }
        }
    }

    public function deleted(Payment $payment): void
    {
        $this->refreshInvoiceStatus($payment);
    }

    protected function refreshInvoiceStatus(Payment $payment): void
    {
        if ($payment->invoice) {
            app(InvoiceStatusUpdater::class)->update($payment->invoice);
        }
    }
}
