<?php

namespace App\Observers;

use App\Models\Quote;
use App\Services\QuoteCalculator;
use App\Services\QuoteNumberGenerator;
use App\Services\Sms\SmsAutomation;

class QuoteObserver
{
    public function __construct(
        protected QuoteNumberGenerator $numberGenerator,
        protected QuoteCalculator $calculator,
        protected SmsAutomation $smsAutomation,
    ) {}

    public function creating(Quote $quote): void
    {
        if (empty($quote->quote_no)) {
            $quote->quote_no = $this->numberGenerator->generate();
        }
    }

    public function saved(Quote $quote): void
    {
        $statusChanged = $quote->wasChanged('status');
        $shouldSendForNewRecord = $quote->wasRecentlyCreated && in_array($quote->status, ['sent', 'accepted', 'rejected'], true);

        $this->calculator->refreshTotals($quote);

        if ($statusChanged) {
            $this->smsAutomation->sendQuoteStatusMessage($quote, $quote->status);
        } elseif ($shouldSendForNewRecord) {
            $this->smsAutomation->sendQuoteStatusMessage($quote, $quote->status);
        }

        if ($quote->status === 'accepted' && ($statusChanged || $shouldSendForNewRecord)) {
            $this->createProjectForAcceptedQuote($quote);
        }
    }

    protected function createProjectForAcceptedQuote(Quote $quote): void
    {
        if ($quote->project()->exists()) {
            return;
        }

        $lead = $quote->lead()->with('client')->first();

        if (! $lead) {
            return;
        }

        $client = $lead->client()->firstOrCreate(
            ['lead_id' => $lead->id],
            [
                'onboarded_at' => now(),
                'status' => 'active',
            ],
        );

        $quote->project()->create([
            'client_id' => $client->id,
            'lead_id' => $lead->id,
            'name' => $lead->company ?: $lead->name,
            'status' => 'active',
            'start_date' => now(),
        ]);
    }
}
