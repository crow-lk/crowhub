<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TermsAndCondition extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'number',
        'parent_id',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Get the parent term (for hierarchical structure)
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(TermsAndCondition::class, 'parent_id');
    }

    /**
     * Get child terms (sub-items)
     */
    public function children(): HasMany
    {
        return $this->hasMany(TermsAndCondition::class, 'parent_id')
            ->orderBy('sort_order')
            ->orderBy('number');
    }

    /**
     * Get all payments associated with this term
     */
    public function payments(): BelongsToMany
    {
        return $this->belongsToMany(Payment::class, 'payment_terms')
            ->withTimestamps();
    }

    /**
     * Scope to get only root level terms (no parent)
     */
    public function scopeRoots($query)
    {
        return $query->whereNull('parent_id')
            ->orderBy('sort_order')
            ->orderBy('number');
    }

    /**
     * Scope to get only active terms
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get formatted display name with number
     */
    public function getDisplayNameAttribute(): string
    {
        $displayText = $this->title ?? $this->content;
        return $this->number ? "{$this->number}. {$displayText}" : $displayText;
    }

    /**
     * Get all descendants (children at all levels)
     */
    public function getAllDescendants(): array
    {
        $descendants = [];
        foreach ($this->children as $child) {
            $descendants[] = $child;
            $descendants = array_merge($descendants, $child->getAllDescendants());
        }
        return $descendants;
    }
}
