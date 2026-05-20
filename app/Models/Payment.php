<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'lead_id',
        'quote_id',
        'social_media_campaign_id',
        'invoice_id',
        'amount',
        'type',
        'paid_date',
        'method',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_date' => 'date',
            'type' => 'string',
        ];
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    public function socialMediaCampaign(): BelongsTo
    {
        return $this->belongsTo(SocialMediaCampaign::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Get the terms and conditions associated with this payment
     */
    public function termsAndConditions(): BelongsToMany
    {
        return $this->belongsToMany(TermsAndCondition::class, 'payment_terms')
            ->withTimestamps();
    }
}
