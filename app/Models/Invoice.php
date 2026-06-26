<?php

namespace App\Models;

use App\Services\Billing\InvoiceNumberGenerator;
use App\Services\Billing\InvoiceStatusUpdater;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_job_id',
        'client_id',
        'lead_id',
        'invoice_no',
        'billing_month',
        'invoice_date',
        'due_date',
        'paid_date',
        'status',
        'subtotal',
        'discount',
        'tax',
        'total',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'billing_month' => 'date',
            'invoice_date' => 'date',
            'due_date' => 'date',
            'paid_date' => 'date',
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'tax' => 'decimal:2',
            'total' => 'decimal:2',
            'status' => 'string',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Invoice $invoice): void {
            if (blank($invoice->invoice_no)) {
                $invoice->invoice_no = app(InvoiceNumberGenerator::class)->generate();
            }

            if ($invoice->billing_month) {
                $invoice->billing_month = Carbon::parse($invoice->billing_month)->startOfMonth();
            }
        });

        static::saved(function (Invoice $invoice): void {
            if ($invoice->wasChanged('status')) {
                ClientActivity::recordFor($invoice, 'invoice', 'Invoice '.$invoice->invoice_no.' marked '.str_replace('_', ' ', $invoice->status));
            }
        });
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(ClientJob::class, 'client_job_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function paidAmount(): float
    {
        return (float) $this->payments()->sum('amount_paid');
    }

    public function balanceDue(): float
    {
        return max((float) $this->total - $this->paidAmount(), 0);
    }

    public function refreshTotals(): void
    {
        $subtotal = (float) $this->items()->sum('amount');

        $this->forceFill([
            'subtotal' => $subtotal,
            'total' => max($subtotal - (float) $this->discount + (float) $this->tax, 0),
        ])->saveQuietly();

        app(InvoiceStatusUpdater::class)->update($this->refresh());
    }
}
