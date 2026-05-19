<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class MaintenanceService extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'category',
        'default_monthly_fee',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'default_monthly_fee' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (MaintenanceService $service): void {
            if (blank($service->slug)) {
                $service->slug = Str::slug($service->name);
            }
        });
    }

    public function contracts(): BelongsToMany
    {
        return $this->belongsToMany(MaintenanceContract::class, 'maintenance_contract_service')
            ->withTimestamps();
    }
}
