<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class ProjectInvoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'invoice_no',
        'invoice_month',
        'amount',
        'status',
        'issued_date',
        'due_date',
        'paid_date',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'invoice_month' => 'date',
            'amount' => 'decimal:2',
            'status' => 'string',
            'issued_date' => 'date',
            'due_date' => 'date',
            'paid_date' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (ProjectInvoice $invoice): void {
            if (blank($invoice->invoice_no)) {
                $invoice->invoice_no = 'PINV-'.now()->format('YmdHis').'-'.Str::upper(Str::random(4));
            }
        });
    }

    public function setInvoiceMonthAttribute(mixed $value): void
    {
        $this->attributes['invoice_month'] = Carbon::parse($value)->startOfMonth()->toDateString();
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ProjectInvoiceItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(ProjectInvoicePayment::class);
    }

    public function paidAmount(): float
    {
        if ($this->relationLoaded('payments')) {
            return (float) $this->payments->sum('amount');
        }

        return (float) $this->payments()->sum('amount');
    }

    public function balanceDue(): float
    {
        return max((float) $this->amount - $this->paidAmount(), 0);
    }
}
