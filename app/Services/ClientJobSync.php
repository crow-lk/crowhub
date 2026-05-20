<?php

namespace App\Services;

use App\Models\ClientActivity;
use App\Models\ClientJob;
use App\Models\MaintenanceContract;
use App\Models\Project;
use App\Models\SocialMediaCampaign;
use Illuminate\Database\Eloquent\Model;

class ClientJobSync
{
    public function sync(Model $record): ?ClientJob
    {
        $payload = $this->payload($record);

        if (! $payload) {
            return null;
        }

        $job = ClientJob::updateOrCreate([
            'jobable_type' => $record::class,
            'jobable_id' => $record->getKey(),
        ], $payload);

        if ($job->wasRecentlyCreated) {
            ClientActivity::recordFor($job, $job->type === 'social_media_campaign' ? 'campaign' : str_replace('_contract', '', $job->type), 'Job created: '.$job->name);
        }

        return $job;
    }

    protected function payload(Model $record): ?array
    {
        if ($record instanceof Project) {
            return [
                'client_id' => $record->client_id,
                'lead_id' => $record->lead_id,
                'name' => $record->name,
                'type' => 'project',
                'status' => $this->normalizeStatus($record->status),
                'start_date' => $record->start_date,
                'end_date' => $record->end_date,
                'description' => $record->description,
                'notes' => $record->notes,
            ];
        }

        if ($record instanceof SocialMediaCampaign) {
            return [
                'client_id' => $record->client_id,
                'lead_id' => $record->lead_id,
                'name' => $record->name,
                'type' => 'social_media_campaign',
                'status' => $this->normalizeStatus($record->status),
                'start_date' => $record->start_date,
                'end_date' => $record->end_date,
                'description' => $record->objective,
                'notes' => $record->notes,
            ];
        }

        if ($record instanceof MaintenanceContract) {
            $lead = $record->lead;

            if (! $lead) {
                return null;
            }

            $client = $lead->client()->firstOrCreate(
                ['lead_id' => $lead->id],
                [
                    'onboarded_at' => $record->start_date ?? now(),
                    'status' => 'active',
                ],
            );

            return [
                'client_id' => $client->id,
                'lead_id' => $lead->id,
                'name' => ($lead->company ?: $lead->name).' Maintenance',
                'type' => 'maintenance_contract',
                'status' => $this->normalizeStatus($record->status),
                'start_date' => $record->start_date,
                'end_date' => null,
                'description' => null,
                'notes' => $record->notes,
            ];
        }

        return null;
    }

    protected function normalizeStatus(?string $status): string
    {
        return match ($status) {
            'planned' => 'planned',
            'paused' => 'paused',
            'completed' => 'completed',
            'cancelled' => 'cancelled',
            default => 'active',
        };
    }
}
