<?php

namespace App\Filament\Resources\Invoices\Pages;

use App\Filament\Resources\Invoices\InvoiceResource;
use App\Models\ClientJob;
use Filament\Resources\Pages\CreateRecord;

class CreateInvoice extends CreateRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $job = ClientJob::find($data['client_job_id']);

        $data['client_id'] = $job->client_id;
        $data['lead_id'] = $job->lead_id;

        return $data;
    }
}
