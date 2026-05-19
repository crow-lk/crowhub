<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'lead_id',
        'onboarded_at',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'onboarded_at' => 'date',
            'status' => 'string',
        ];
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function socialMediaCampaigns(): HasMany
    {
        return $this->hasMany(SocialMediaCampaign::class);
    }
}
