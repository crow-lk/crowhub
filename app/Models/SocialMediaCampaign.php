<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class SocialMediaCampaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'lead_id',
        'maintenance_contract_id',
        'name',
        'status',
        'platforms',
        'start_date',
        'end_date',
        'budget',
        'objective',
        'content_plan',
        'results_summary',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'string',
            'platforms' => 'array',
            'start_date' => 'date',
            'end_date' => 'date',
            'budget' => 'decimal:2',
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

    public function maintenanceContract(): BelongsTo
    {
        return $this->belongsTo(MaintenanceContract::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function job(): MorphOne
    {
        return $this->morphOne(ClientJob::class, 'jobable');
    }

    public function invoices(): HasManyThrough
    {
        return $this->hasManyThrough(Invoice::class, ClientJob::class, 'jobable_id', 'client_job_id')
            ->where('client_jobs.jobable_type', self::class);
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
        return max((float) $this->budget - $this->paidAmount(), 0);
    }
}
