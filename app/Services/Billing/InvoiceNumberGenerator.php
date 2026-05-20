<?php

namespace App\Services\Billing;

use App\Models\Invoice;

class InvoiceNumberGenerator
{
    public function generate(): string
    {
        $prefix = 'INV-'.now()->format('Ym').'-';
        $next = Invoice::query()
            ->where('invoice_no', 'like', $prefix.'%')
            ->count() + 1;

        return $prefix.str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }
}
