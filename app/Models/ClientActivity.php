<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ClientActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'lead_id',
        'client_job_id',
        'subject_type',
        'subject_id',
        'type',
        'title',
        'description',
        'activity_date',
    ];

    protected function casts(): array
    {
        return [
            'activity_date' => 'datetime',
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

    public function job(): BelongsTo
    {
        return $this->belongsTo(ClientJob::class, 'client_job_id');
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public static function recordFor(Model $subject, string $type, string $title, ?string $description = null): ?self
    {
        [$client, $lead, $job] = self::resolveContext($subject);

        if (! $client) {
            return null;
        }

        return self::create([
            'client_id' => $client->id,
            'lead_id' => $lead?->id,
            'client_job_id' => $job?->id,
            'subject_type' => $subject::class,
            'subject_id' => $subject->getKey(),
            'type' => $type,
            'title' => $title,
            'description' => $description,
            'activity_date' => now(),
        ]);
    }

    protected static function resolveContext(Model $subject): array
    {
        if ($subject instanceof ClientJob) {
            return [$subject->client, $subject->lead, $subject];
        }

        if ($subject instanceof Invoice) {
            return [$subject->client, $subject->lead, $subject->job];
        }

        if ($subject instanceof Payment && $subject->invoice) {
            return [$subject->invoice->client, $subject->invoice->lead, $subject->invoice->job];
        }

        foreach (['client', 'lead', 'job'] as $relation) {
            if (! method_exists($subject, $relation)) {
                continue;
            }
        }

        $client = method_exists($subject, 'client') ? $subject->client : null;
        $lead = method_exists($subject, 'lead') ? $subject->lead : null;
        $job = method_exists($subject, 'job') ? $subject->job : null;

        return [$client, $lead, $job];
    }
}
