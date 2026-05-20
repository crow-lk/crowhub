<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ClientJob extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'lead_id',
        'jobable_type',
        'jobable_id',
        'name',
        'type',
        'status',
        'start_date',
        'end_date',
        'description',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'status' => 'string',
            'type' => 'string',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function jobable(): MorphTo
    {
        return $this->morphTo();
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(ClientActivity::class);
    }

    public function payments(): HasManyThrough
    {
        return $this->hasManyThrough(Payment::class, Invoice::class, 'client_job_id', 'invoice_id');
    }

    public function invoiceTotal(): float
    {
        return (float) $this->invoices()->where('status', '!=', 'cancelled')->sum('total');
    }

    public function paidTotal(): float
    {
        return (float) Payment::query()
            ->whereIn('invoice_id', $this->invoices()->select('id'))
            ->sum('amount');
    }

    public function balanceDue(): float
    {
        return max($this->invoiceTotal() - $this->paidTotal(), 0);
    }
}
