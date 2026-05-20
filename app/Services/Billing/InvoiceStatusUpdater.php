<?php

namespace App\Services\Billing;

use App\Models\Invoice;

class InvoiceStatusUpdater
{
    public function update(Invoice $invoice): void
    {
        if ($invoice->status === 'cancelled') {
            return;
        }

        $paid = $invoice->paidAmount();
        $total = (float) $invoice->total;

        if ($total > 0 && $paid >= $total) {
            $invoice->forceFill([
                'status' => 'paid',
                'paid_date' => $invoice->paid_date ?? now(),
            ])->saveQuietly();

            return;
        }

        if ($paid > 0) {
            $invoice->forceFill([
                'status' => 'partially_paid',
                'paid_date' => null,
            ])->saveQuietly();

            return;
        }

        if ($invoice->status === 'paid' || $invoice->status === 'partially_paid') {
            $invoice->forceFill([
                'status' => 'sent',
                'paid_date' => null,
            ])->saveQuietly();
        }
    }
}
