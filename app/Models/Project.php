<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'lead_id',
        'quote_id',
        'name',
        'status',
        'start_date',
        'end_date',
        'github_project_url',
        'github_repo_url',
        'description',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'string',
            'start_date' => 'date',
            'end_date' => 'date',
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

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(ProjectTask::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(ProjectInvoice::class);
    }
}
