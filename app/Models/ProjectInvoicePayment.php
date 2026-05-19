<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectInvoicePayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_invoice_id',
        'amount',
        'paid_date',
        'method',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_date' => 'date',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(ProjectInvoice::class, 'project_invoice_id');
    }
}
