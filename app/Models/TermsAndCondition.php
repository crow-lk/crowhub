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
        'content',
        'number',
        'secondary_terms',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
            'secondary_terms' => 'array',
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
        return $this->number ? "{$this->number}. {$this->content}" : $this->content;
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

    /**
     * Get formatted terms for display with auto-generated numbering
     * Returns array of terms with formatted numbers (1, 1.1, 1.2, etc.)
     */
    public function getFormattedTerms(): array
    {
        $formattedTerms = [];

        // Add main term as number 1
        $formattedTerms[] = [
            'number' => '1',
            'content' => $this->content,
            'is_main' => true,
        ];

        // Add secondary terms with numbering 1.1, 1.2, etc.
        $secondaryTerms = $this->secondary_terms ?? [];
        foreach ($secondaryTerms as $index => $secondary) {
            $formattedTerms[] = [
                'number' => '1.' . ($index + 1),
                'content' => $secondary['content'] ?? '',
                'is_main' => false,
            ];
        }

        return $formattedTerms;
    }

    /**
     * Static method to get all terms formatted for a list of term IDs
     * Used when displaying terms in quote/invoice
     * Main term: 1, 2, 3...
     * Secondary terms: 1.1, 1.2, 2.1, 2.2...
     */
    public static function getFormattedTermsForIds(array $termIds): array
    {
        $allTerms = [];
        $mainNumber = 1;

        foreach ($termIds as $termId) {
            $term = self::find($termId);
            if (!$term) continue;

            // Get formatted terms for this main term
            $formatted = $term->getFormattedTerms();

            // Renumber based on position in the list
            foreach ($formatted as &$item) {
                $originalNumber = $item['number'];
                if ($originalNumber === '1') {
                    // Main term: 1, 2, 3, etc.
                    $item['number'] = (string) $mainNumber;
                } else {
                    // Secondary term: 1.1, 1.2, 2.1, 2.2, etc.
                    $item['number'] = $mainNumber . '.' . substr($originalNumber, 2);
                }
                $allTerms[] = $item;
            }
            $mainNumber++;
        }

        return $allTerms;
    }
}
