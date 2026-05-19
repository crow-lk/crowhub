<?php

namespace Tests\Feature\SocialMediaCampaigns;

use App\Filament\Resources\SocialMediaCampaigns\Pages\ListSocialMediaCampaigns;
use App\Models\Client;
use App\Models\Lead;
use App\Models\MaintenanceContract;
use App\Models\Payment;
use App\Models\SocialMediaCampaign;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SocialMediaCampaignTest extends TestCase
{
    use RefreshDatabase;

    public function test_social_media_campaign_can_be_recorded_as_client_job_with_optional_contract(): void
    {
        [$lead, $client, $contract] = $this->createEngagement();

        $campaign = SocialMediaCampaign::create([
            'client_id' => $client->id,
            'lead_id' => $lead->id,
            'maintenance_contract_id' => $contract->id,
            'name' => 'Pensala May Awareness Campaign',
            'status' => 'active',
            'platforms' => ['facebook', 'instagram'],
            'start_date' => now()->startOfMonth(),
            'end_date' => now()->endOfMonth(),
            'budget' => 75000,
            'objective' => 'Increase inbound inquiries',
            'content_plan' => 'Weekly reels and boosted posts.',
        ]);

        $this->assertDatabaseHas('social_media_campaigns', [
            'id' => $campaign->id,
            'client_id' => $client->id,
            'maintenance_contract_id' => $contract->id,
            'name' => 'Pensala May Awareness Campaign',
            'status' => 'active',
            'budget' => 75000,
        ]);
        $this->assertSame(['facebook', 'instagram'], $campaign->fresh()->platforms);
    }

    public function test_social_media_campaign_resource_index_renders(): void
    {
        $this->actingAs(User::factory()->create());

        [$lead, $client, $contract] = $this->createEngagement();

        SocialMediaCampaign::create([
            'client_id' => $client->id,
            'lead_id' => $lead->id,
            'maintenance_contract_id' => $contract->id,
            'name' => 'Pensala May Awareness Campaign',
            'status' => 'planned',
            'platforms' => ['facebook'],
        ]);

        Livewire::test(ListSocialMediaCampaigns::class)
            ->assertSee('Pensala May Awareness Campaign')
            ->assertSee('Planned');
    }

    public function test_social_media_campaign_payments_use_existing_payments_table(): void
    {
        [$lead, $client, $contract] = $this->createEngagement();

        $campaign = SocialMediaCampaign::create([
            'client_id' => $client->id,
            'lead_id' => $lead->id,
            'maintenance_contract_id' => $contract->id,
            'name' => 'Pensala May Awareness Campaign',
            'status' => 'active',
            'platforms' => ['facebook'],
            'budget' => 75000,
        ]);

        Payment::create([
            'lead_id' => $lead->id,
            'social_media_campaign_id' => $campaign->id,
            'amount' => 25000,
            'type' => 'advance',
            'paid_date' => now(),
            'method' => 'Bank transfer',
        ]);

        $this->assertSame(25000.0, $campaign->fresh()->paidAmount());
        $this->assertSame(50000.0, $campaign->fresh()->balanceDue());
        $this->assertDatabaseHas('payments', [
            'lead_id' => $lead->id,
            'social_media_campaign_id' => $campaign->id,
            'amount' => 25000,
            'type' => 'advance',
        ]);
    }

    protected function createEngagement(): array
    {
        $lead = Lead::create([
            'name' => 'Nimal Perera',
            'email' => fake()->safeEmail(),
            'phone' => '94710000000',
            'company' => 'Pensala',
            'status' => 'won',
            'source' => 'manual',
        ]);

        $client = Client::create([
            'lead_id' => $lead->id,
            'onboarded_at' => now(),
            'status' => 'active',
        ]);

        $contract = MaintenanceContract::create([
            'lead_id' => $lead->id,
            'start_date' => now(),
            'monthly_fee' => 50000,
            'billing_day' => 5,
            'status' => 'active',
        ]);

        return [$lead, $client, $contract];
    }
}
