<?php

namespace App\Services\Billing;

use App\Models\ClientActivity;
use App\Models\Invoice;
use App\Models\MaintenanceContract;
use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\SocialMediaCampaign;
use App\Services\ClientJobSync;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class InvoiceCreator
{
    public function forProjectTasks(Project $project, Collection $tasks, array $data): Invoice
    {
        $job = app(ClientJobSync::class)->sync($project);

        if (! $job) {
            throw ValidationException::withMessages(['project' => 'Project does not have a linked job.']);
        }

        $invoice = Invoice::create([
            'client_job_id' => $job->id,
            'client_id' => $project->client_id,
            'lead_id' => $project->lead_id,
            'billing_month' => Carbon::parse($data['billing_month'] ?? now())->startOfMonth(),
            'invoice_date' => $data['invoice_date'],
            'due_date' => $data['due_date'],
            'status' => 'draft',
            'notes' => $data['notes'] ?? null,
        ]);

        foreach ($tasks as $task) {
            $item = $invoice->items()->create([
                'source_type' => ProjectTask::class,
                'source_id' => $task->id,
                'description' => $task->title,
                'quantity' => 1,
                'unit_price' => $task->amount,
                'amount' => $task->amount,
            ]);

            $task->update([
                'invoice_item_id' => $item->id,
                'status' => 'invoiced',
                'invoiced_at' => now(),
            ]);
        }

        $invoice->refreshTotals();
        ClientActivity::recordFor($invoice, 'invoice', 'Invoice created from project tasks: '.$invoice->invoice_no);

        return $invoice->refresh();
    }

    public function forCampaign(SocialMediaCampaign $campaign, array $data): Invoice
    {
        $job = app(ClientJobSync::class)->sync($campaign);

        if (! $job) {
            throw ValidationException::withMessages(['campaign' => 'Campaign does not have a linked job.']);
        }

        $amount = (float) ($data['amount'] ?? $campaign->budget ?? 0);

        $invoice = Invoice::create([
            'client_job_id' => $job->id,
            'client_id' => $campaign->client_id,
            'lead_id' => $campaign->lead_id,
            'billing_month' => filled($data['billing_month'] ?? null) ? Carbon::parse($data['billing_month'])->startOfMonth() : null,
            'invoice_date' => $data['invoice_date'],
            'due_date' => $data['due_date'],
            'status' => 'draft',
            'notes' => $data['notes'] ?? null,
        ]);

        $invoice->items()->create([
            'source_type' => SocialMediaCampaign::class,
            'source_id' => $campaign->id,
            'description' => $data['description'] ?? $campaign->name,
            'quantity' => 1,
            'unit_price' => $amount,
            'amount' => $amount,
        ]);

        $invoice->refreshTotals();
        ClientActivity::recordFor($invoice, 'invoice', 'Campaign invoice created: '.$invoice->invoice_no);

        return $invoice->refresh();
    }

    public function forMaintenanceMonth(MaintenanceContract $contract, array $data): Invoice
    {
        $job = app(ClientJobSync::class)->sync($contract);

        if (! $job) {
            throw ValidationException::withMessages(['contract' => 'Maintenance contract does not have a linked job.']);
        }

        $month = Carbon::parse($data['billing_month'] ?? now())->startOfMonth();

        if ($job->invoices()->whereDate('billing_month', $month)->exists()) {
            throw ValidationException::withMessages(['billing_month' => 'This maintenance month already has an invoice.']);
        }

        $invoice = Invoice::create([
            'client_job_id' => $job->id,
            'client_id' => $job->client_id,
            'lead_id' => $job->lead_id,
            'billing_month' => $month,
            'invoice_date' => $data['invoice_date'],
            'due_date' => $data['due_date'],
            'status' => 'draft',
            'notes' => $data['notes'] ?? null,
        ]);

        $invoice->items()->create([
            'source_type' => MaintenanceContract::class,
            'source_id' => $contract->id,
            'description' => 'Maintenance services for '.$month->format('F Y'),
            'quantity' => 1,
            'unit_price' => $contract->monthly_fee,
            'amount' => $contract->monthly_fee,
        ]);

        $invoice->refreshTotals();
        ClientActivity::recordFor($invoice, 'invoice', 'Maintenance invoice created: '.$invoice->invoice_no);

        return $invoice->refresh();
    }
}
