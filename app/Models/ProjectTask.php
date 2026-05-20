<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ProjectTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'invoice_item_id',
        'github_item_id',
        'github_content_id',
        'github_task_url',
        'github_status',
        'github_assignees',
        'github_synced_at',
        'title',
        'description',
        'status',
        'amount',
        'completed_at',
        'invoiced_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'string',
            'github_assignees' => 'array',
            'github_synced_at' => 'datetime',
            'amount' => 'decimal:2',
            'completed_at' => 'date',
            'invoiced_at' => 'date',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function invoiceItem(): HasOne
    {
        return $this->hasOne(ProjectInvoiceItem::class);
    }

    public function unifiedInvoiceItem(): BelongsTo
    {
        return $this->belongsTo(InvoiceItem::class, 'invoice_item_id');
    }
}
