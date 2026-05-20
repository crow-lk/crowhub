<?php

namespace Tests\Feature\Billing;

use App\Filament\Resources\Invoices\Pages\ListInvoices;
use App\Filament\Resources\Jobs\Pages\ListJobs;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\MaintenanceContract;
use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\SocialMediaCampaign;
use App\Models\User;
use App\Services\Billing\InvoiceCreator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class UnifiedJobsInvoicesTest extends TestCase
{
    use RefreshDatabase;

    public function test_work_records_create_client_jobs(): void
    {
        [$lead, $client] = $this->createClientLead();

        $project = Project::create([
            'client_id' => $client->id,
            'lead_id' => $lead->id,
            'name' => 'Pensala App',
            'status' => 'active',
            'start_date' => now(),
        ]);

        $campaign = SocialMediaCampaign::create([
            'client_id' => $client->id,
            'lead_id' => $lead->id,
            'name' => 'May Awareness Campaign',
            'status' => 'active',
            'budget' => 60000,
        ]);

        $contract = MaintenanceContract::create([
            'lead_id' => $lead->id,
            'start_date' => now(),
            'monthly_fee' => 35000,
            'billing_day' => 1,
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('client_jobs', [
            'jobable_type' => Project::class,
            'jobable_id' => $project->id,
            'type' => 'project',
            'name' => 'Pensala App',
        ]);
        $this->assertDatabaseHas('client_jobs', [
            'jobable_type' => SocialMediaCampaign::class,
            'jobable_id' => $campaign->id,
            'type' => 'social_media_campaign',
        ]);
        $this->assertDatabaseHas('client_jobs', [
            'jobable_type' => MaintenanceContract::class,
            'jobable_id' => $contract->id,
            'type' => 'maintenance_contract',
        ]);
    }

    public function test_project_tasks_create_unified_invoice_and_prevent_reinvoice(): void
    {
        [$lead, $client] = $this->createClientLead();
        $project = Project::create([
            'client_id' => $client->id,
            'lead_id' => $lead->id,
            'name' => 'Pensala App',
            'status' => 'active',
            'start_date' => now(),
        ]);
        $task = ProjectTask::create([
            'project_id' => $project->id,
            'title' => 'Build dashboard',
            'status' => 'done',
            'amount' => 50000,
            'completed_at' => now(),
        ]);

        $invoice = app(InvoiceCreator::class)->forProjectTasks($project, collect([$task]), [
            'billing_month' => now()->startOfMonth(),
            'invoice_date' => now(),
            'due_date' => now()->addDays(7),
        ]);

        $this->assertSame(50000.0, (float) $invoice->total);
        $this->assertDatabaseHas('project_tasks', [
            'id' => $task->id,
            'status' => 'invoiced',
        ]);
        $this->assertNotNull($task->fresh()->invoice_item_id);
        $this->assertSame(0, $project->tasks()->where('status', 'done')->whereNull('invoice_item_id')->count());
    }

    public function test_invoice_payments_update_status_and_activity(): void
    {
        [$lead, $client] = $this->createClientLead();
        $project = Project::create([
            'client_id' => $client->id,
            'lead_id' => $lead->id,
            'name' => 'Pensala App',
            'status' => 'active',
            'start_date' => now(),
        ]);
        $job = $project->job()->first();

        $invoice = Invoice::create([
            'client_job_id' => $job->id,
            'client_id' => $client->id,
            'lead_id' => $lead->id,
            'invoice_date' => now(),
            'due_date' => now()->addDays(7),
            'status' => 'sent',
        ]);
        $invoice->items()->create([
            'description' => 'Development work',
            'quantity' => 1,
            'unit_price' => 100000,
            'amount' => 100000,
        ]);

        $invoice->payments()->create([
            'lead_id' => $lead->id,
            'amount' => 40000,
            'type' => 'other',
            'paid_date' => now(),
        ]);

        $this->assertSame('partially_paid', $invoice->fresh()->status);

        $invoice->payments()->create([
            'lead_id' => $lead->id,
            'amount' => 60000,
            'type' => 'other',
            'paid_date' => now(),
        ]);

        $this->assertSame('paid', $invoice->fresh()->status);
        $this->assertDatabaseHas('client_activities', [
            'client_id' => $client->id,
            'type' => 'payment',
        ]);
    }

    public function test_duplicate_maintenance_month_invoice_is_prevented(): void
    {
        [$lead] = $this->createClientLead();
        $contract = MaintenanceContract::create([
            'lead_id' => $lead->id,
            'start_date' => now(),
            'monthly_fee' => 35000,
            'billing_day' => 1,
            'status' => 'active',
        ]);

        $data = [
            'billing_month' => now()->startOfMonth(),
            'invoice_date' => now(),
            'due_date' => now()->addDays(7),
        ];

        app(InvoiceCreator::class)->forMaintenanceMonth($contract, $data);

        $this->expectException(ValidationException::class);

        app(InvoiceCreator::class)->forMaintenanceMonth($contract, $data);
    }

    public function test_jobs_and_invoices_resources_render(): void
    {
        $this->actingAs(User::factory()->create());

        [$lead, $client] = $this->createClientLead();
        Project::create([
            'client_id' => $client->id,
            'lead_id' => $lead->id,
            'name' => 'Pensala App',
            'status' => 'active',
            'start_date' => now(),
        ]);

        Livewire::test(ListJobs::class)
            ->assertSee('Pensala App');

        Livewire::test(ListInvoices::class)
            ->assertOk();
    }

    protected function createClientLead(): array
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

        return [$lead, $client];
    }
}
