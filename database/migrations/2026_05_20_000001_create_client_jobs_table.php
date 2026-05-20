<?php

use App\Models\Client;
use App\Models\Lead;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Client::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Lead::class)->nullable()->constrained()->nullOnDelete();
            $table->morphs('jobable');
            $table->string('name')->index();
            $table->enum('type', ['project', 'social_media_campaign', 'maintenance_contract'])->index();
            $table->enum('status', ['planned', 'active', 'paused', 'completed', 'cancelled'])->default('active')->index();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->text('description')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['jobable_type', 'jobable_id']);
            $table->index(['client_id', 'status']);
        });

        $now = now();

        foreach (DB::table('projects')->get() as $project) {
            DB::table('client_jobs')->insertOrIgnore([
                'client_id' => $project->client_id,
                'lead_id' => $project->lead_id,
                'jobable_type' => App\Models\Project::class,
                'jobable_id' => $project->id,
                'name' => $project->name,
                'type' => 'project',
                'status' => $this->normalizeStatus($project->status),
                'start_date' => $project->start_date,
                'end_date' => $project->end_date,
                'description' => $project->description,
                'notes' => $project->notes,
                'created_at' => $project->created_at ?? $now,
                'updated_at' => $project->updated_at ?? $now,
            ]);
        }

        foreach (DB::table('social_media_campaigns')->get() as $campaign) {
            DB::table('client_jobs')->insertOrIgnore([
                'client_id' => $campaign->client_id,
                'lead_id' => $campaign->lead_id,
                'jobable_type' => App\Models\SocialMediaCampaign::class,
                'jobable_id' => $campaign->id,
                'name' => $campaign->name,
                'type' => 'social_media_campaign',
                'status' => $this->normalizeStatus($campaign->status),
                'start_date' => $campaign->start_date,
                'end_date' => $campaign->end_date,
                'description' => $campaign->objective,
                'notes' => $campaign->notes,
                'created_at' => $campaign->created_at ?? $now,
                'updated_at' => $campaign->updated_at ?? $now,
            ]);
        }

        foreach (DB::table('maintenance_contracts')->get() as $contract) {
            $client = DB::table('clients')->where('lead_id', $contract->lead_id)->first();

            if (! $client) {
                $clientId = DB::table('clients')->insertGetId([
                    'lead_id' => $contract->lead_id,
                    'onboarded_at' => $contract->start_date,
                    'status' => 'active',
                    'notes' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            } else {
                $clientId = $client->id;
            }

            $lead = DB::table('leads')->find($contract->lead_id);

            DB::table('client_jobs')->insertOrIgnore([
                'client_id' => $clientId,
                'lead_id' => $contract->lead_id,
                'jobable_type' => App\Models\MaintenanceContract::class,
                'jobable_id' => $contract->id,
                'name' => ($lead?->company ?: $lead?->name ?: 'Maintenance Contract').' Maintenance',
                'type' => 'maintenance_contract',
                'status' => $this->normalizeStatus($contract->status),
                'start_date' => $contract->start_date,
                'end_date' => null,
                'description' => null,
                'notes' => $contract->notes,
                'created_at' => $contract->created_at ?? $now,
                'updated_at' => $contract->updated_at ?? $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('client_jobs');
    }

    private function normalizeStatus(?string $status): string
    {
        return match ($status) {
            'planned' => 'planned',
            'paused' => 'paused',
            'completed' => 'completed',
            'cancelled' => 'cancelled',
            default => 'active',
        };
    }
};
