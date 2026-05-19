<?php

namespace Tests\Feature\Projects;

use App\Filament\Resources\Projects\Pages\ListProjects;
use App\Models\Client;
use App\Models\Lead;
use App\Models\Project;
use App\Models\ProjectInvoice;
use App\Models\ProjectInvoiceItem;
use App\Models\ProjectInvoicePayment;
use App\Models\ProjectTask;
use App\Models\Quote;
use App\Models\User;
use App\Services\GitHub\GitHubProjectSync;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class ProjectManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_accepting_quote_creates_client_and_project_once(): void
    {
        $lead = Lead::create([
            'name' => 'Nimal Perera',
            'email' => 'nimal@example.com',
            'phone' => '94710000000',
            'company' => 'Pensala',
            'status' => 'quoted',
            'source' => 'manual',
        ]);

        $quote = Quote::create([
            'quote_no' => 'Q-PENSALA-001',
            'lead_id' => $lead->id,
            'status' => 'sent',
            'valid_until' => now()->addDays(14),
            'subtotal' => 150000,
            'discount' => 0,
            'total' => 150000,
        ]);

        $quote->update(['status' => 'accepted']);
        $quote->refresh()->update(['valid_until' => now()->addDays(30)]);

        $client = Client::where('lead_id', $lead->id)->first();

        $this->assertNotNull($client);
        $this->assertDatabaseHas('projects', [
            'client_id' => $client->id,
            'lead_id' => $lead->id,
            'quote_id' => $quote->id,
            'name' => 'Pensala',
            'status' => 'active',
        ]);
        $this->assertSame(1, Project::where('quote_id', $quote->id)->count());
    }

    public function test_project_tasks_can_be_charged_through_invoice_items(): void
    {
        $project = $this->createProject();

        $task = ProjectTask::create([
            'project_id' => $project->id,
            'github_task_url' => 'https://github.com/orgs/example/projects/1/views/1?pane=issue&itemId=100',
            'title' => 'Build Pensala dashboard',
            'status' => 'done',
            'amount' => 50000,
            'completed_at' => now(),
        ]);

        $invoice = ProjectInvoice::create([
            'project_id' => $project->id,
            'invoice_month' => now()->startOfMonth(),
            'amount' => 50000,
            'status' => 'draft',
            'issued_date' => now(),
            'due_date' => now()->day(5),
        ]);

        ProjectInvoiceItem::create([
            'project_invoice_id' => $invoice->id,
            'project_task_id' => $task->id,
            'description' => $task->title,
            'amount' => $task->amount,
        ]);

        $task->update([
            'status' => 'invoiced',
            'invoiced_at' => now(),
        ]);

        $invoice->update(['status' => 'sent']);
        $invoice->update(['status' => 'paid', 'paid_date' => now()]);

        $this->assertNotEmpty($invoice->fresh()->invoice_no);
        $this->assertDatabaseHas('project_invoices', [
            'id' => $invoice->id,
            'status' => 'paid',
        ]);
        $this->assertDatabaseHas('project_invoice_items', [
            'project_invoice_id' => $invoice->id,
            'project_task_id' => $task->id,
            'amount' => 50000,
        ]);
        $this->assertDatabaseHas('project_tasks', [
            'id' => $task->id,
            'status' => 'invoiced',
        ]);
    }

    public function test_project_invoice_payments_track_paid_amount_and_balance(): void
    {
        $project = $this->createProject();
        $invoice = ProjectInvoice::create([
            'project_id' => $project->id,
            'invoice_month' => now()->startOfMonth(),
            'amount' => 50000,
            'status' => 'sent',
            'issued_date' => now(),
            'due_date' => now()->addDays(7),
        ]);

        ProjectInvoicePayment::create([
            'project_invoice_id' => $invoice->id,
            'amount' => 20000,
            'paid_date' => now(),
            'method' => 'Bank transfer',
        ]);

        $this->assertSame(20000.0, $invoice->fresh()->paidAmount());
        $this->assertSame(30000.0, $invoice->fresh()->balanceDue());

        ProjectInvoicePayment::create([
            'project_invoice_id' => $invoice->id,
            'amount' => 30000,
            'paid_date' => now(),
            'method' => 'Bank transfer',
        ]);

        $invoice->refresh();

        if ($invoice->balanceDue() <= 0) {
            $invoice->update([
                'status' => 'paid',
                'paid_date' => now(),
            ]);
        }

        $this->assertSame(0.0, $invoice->fresh()->balanceDue());
        $this->assertDatabaseHas('project_invoices', [
            'id' => $invoice->id,
            'status' => 'paid',
        ]);
    }

    public function test_project_invoice_pdf_can_be_rendered(): void
    {
        $this->actingAs(User::factory()->create());

        $project = $this->createProject();
        $task = ProjectTask::create([
            'project_id' => $project->id,
            'title' => 'Build Pensala dashboard',
            'status' => 'invoiced',
            'amount' => 50000,
            'completed_at' => now(),
            'invoiced_at' => now(),
        ]);
        $invoice = ProjectInvoice::create([
            'project_id' => $project->id,
            'invoice_month' => now()->startOfMonth(),
            'amount' => 50000,
            'status' => 'sent',
            'issued_date' => now(),
            'due_date' => now()->addDays(7),
        ]);

        ProjectInvoiceItem::create([
            'project_invoice_id' => $invoice->id,
            'project_task_id' => $task->id,
            'description' => $task->title,
            'amount' => $task->amount,
        ]);

        $this->get(route('admin.project-invoices.show', $invoice))
            ->assertOk();
    }

    public function test_project_resource_index_renders_with_github_links(): void
    {
        $this->actingAs(User::factory()->create());

        $this->createProject([
            'github_project_url' => 'https://github.com/orgs/example/projects/1',
            'github_repo_url' => 'https://github.com/example/pensala',
        ]);

        Livewire::test(ListProjects::class)
            ->assertSee('Pensala')
            ->assertSee('GitHub Project')
            ->assertSee('GitHub Repo');
    }

    public function test_github_project_sync_imports_project_items_read_only(): void
    {
        config(['services.github.token' => 'github-token']);

        $project = $this->createProject([
            'github_project_url' => 'https://github.com/orgs/crowlk/projects/7',
        ]);

        $existingTask = ProjectTask::create([
            'project_id' => $project->id,
            'github_item_id' => 'PVTI_existing',
            'github_task_url' => 'https://github.com/crowlk/pensala/issues/9',
            'title' => 'Old title',
            'status' => 'done',
            'amount' => 25000,
            'completed_at' => now()->subDay(),
        ]);

        Http::fake([
            'https://api.github.com/graphql' => Http::sequence()
                ->push([
                    'data' => [
                        'organization' => [
                            'projectV2' => [
                                'id' => 'PVT_project',
                            ],
                        ],
                    ],
                ])
                ->push([
                    'data' => [
                        'node' => [
                            'items' => [
                                'pageInfo' => [
                                    'hasNextPage' => false,
                                    'endCursor' => null,
                                ],
                                'nodes' => [
                                    [
                                        'id' => 'PVTI_existing',
                                        'updatedAt' => now()->toISOString(),
                                        'fieldValues' => [
                                            'nodes' => [
                                                [
                                                    'name' => 'Done',
                                                    'field' => ['name' => 'Status'],
                                                ],
                                            ],
                                        ],
                                        'content' => [
                                            'id' => 'I_existing',
                                            'title' => 'Updated GitHub issue',
                                            'url' => 'https://github.com/crowlk/pensala/issues/9',
                                            'body' => 'Imported from GitHub.',
                                            'state' => 'OPEN',
                                            'closedAt' => null,
                                            'assignees' => [
                                                'nodes' => [
                                                    ['login' => 'kaviya'],
                                                ],
                                            ],
                                        ],
                                    ],
                                    [
                                        'id' => 'PVTI_new',
                                        'updatedAt' => now()->toISOString(),
                                        'fieldValues' => [
                                            'nodes' => [
                                                [
                                                    'name' => 'In Progress',
                                                    'field' => ['name' => 'Status'],
                                                ],
                                            ],
                                        ],
                                        'content' => [
                                            'id' => 'I_new',
                                            'title' => 'New GitHub issue',
                                            'url' => 'https://github.com/crowlk/pensala/issues/10',
                                            'body' => 'New task.',
                                            'state' => 'OPEN',
                                            'closedAt' => null,
                                            'assignees' => ['nodes' => []],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ]),
        ]);

        $result = app(GitHubProjectSync::class)->sync($project);

        $this->assertSame(['created' => 1, 'updated' => 1, 'skipped' => 0], $result);
        $this->assertDatabaseHas('project_tasks', [
            'id' => $existingTask->id,
            'title' => 'Updated GitHub issue',
            'status' => 'done',
            'amount' => 25000,
            'github_status' => 'Done',
        ]);
        $this->assertDatabaseHas('project_tasks', [
            'project_id' => $project->id,
            'github_item_id' => 'PVTI_new',
            'title' => 'New GitHub issue',
            'status' => 'in_progress',
            'amount' => 0,
            'github_status' => 'In Progress',
        ]);
    }

    protected function createProject(array $attributes = []): Project
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

        return Project::create([
            'client_id' => $client->id,
            'lead_id' => $lead->id,
            'name' => 'Pensala',
            'status' => 'active',
            'start_date' => now(),
            ...$attributes,
        ]);
    }
}
