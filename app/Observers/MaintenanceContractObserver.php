<?php

namespace App\Observers;

use App\Models\MaintenanceContract;
use App\Services\ClientJobSync;
use App\Services\Sms\SmsAutomation;

class MaintenanceContractObserver
{
    public function __construct(protected SmsAutomation $automation) {}

    public function created(MaintenanceContract $contract): void
    {
        app(ClientJobSync::class)->sync($contract);
        $this->automation->sendSupportWelcome($contract);
    }

    public function saved(MaintenanceContract $contract): void
    {
        app(ClientJobSync::class)->sync($contract);
    }
}
