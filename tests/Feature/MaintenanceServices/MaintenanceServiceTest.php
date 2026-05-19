<?php

namespace Tests\Feature\MaintenanceServices;

use App\Filament\Resources\MaintenanceContracts\Pages\ListMaintenanceContracts;
use App\Filament\Resources\MaintenanceServices\Pages\ListMaintenanceServices;
use App\Models\Lead;
use App\Models\MaintenanceContract;
use App\Models\MaintenanceService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MaintenanceServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_maintenance_services_are_available(): void
    {
        $this->assertDatabaseHas('maintenance_services', [
            'name' => 'Social Media Campaigns',
            'category' => 'Marketing',
            'is_active' => true,
        ]);
        $this->assertDatabaseHas('maintenance_services', [
            'name' => 'Website Management',
            'category' => 'Web',
            'is_active' => true,
        ]);
    }

    public function test_contract_can_include_multiple_service_types(): void
    {
        $lead = Lead::create([
            'name' => 'Nimal Perera',
            'email' => fake()->safeEmail(),
            'phone' => '94710000000',
            'company' => 'Pensala',
            'status' => 'won',
            'source' => 'manual',
        ]);

        $contract = MaintenanceContract::create([
            'lead_id' => $lead->id,
            'start_date' => now(),
            'monthly_fee' => 85000,
            'billing_day' => 5,
            'status' => 'active',
        ]);

        $serviceIds = MaintenanceService::query()
            ->whereIn('name', ['Social Media Campaigns', 'Website Management'])
            ->pluck('id');

        $contract->services()->sync($serviceIds);

        $this->assertSame(
            ['Social Media Campaigns', 'Website Management'],
            $contract->fresh()->services()->orderBy('name')->pluck('name')->all(),
        );
    }

    public function test_maintenance_service_resource_index_renders(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(ListMaintenanceServices::class)
            ->assertSee('Social Media Campaigns')
            ->assertSee('Website Management');
    }

    public function test_maintenance_contract_index_renders_service_names(): void
    {
        $this->actingAs(User::factory()->create());

        $lead = Lead::create([
            'name' => 'Nimal Perera',
            'email' => fake()->safeEmail(),
            'phone' => '94710000000',
            'company' => 'Pensala',
            'status' => 'won',
            'source' => 'manual',
        ]);

        $contract = MaintenanceContract::create([
            'lead_id' => $lead->id,
            'start_date' => now(),
            'monthly_fee' => 85000,
            'billing_day' => 5,
            'status' => 'active',
        ]);

        $contract->services()->sync(
            MaintenanceService::whereIn('name', ['Social Media Campaigns', 'Website Management'])->pluck('id'),
        );

        Livewire::test(ListMaintenanceContracts::class)
            ->assertSee('Social Media Campaigns')
            ->assertSee('Website Management');
    }
}
