<?php

namespace App\Observers;

use App\Models\ClientActivity;
use App\Models\SocialMediaCampaign;
use App\Services\ClientJobSync;

class SocialMediaCampaignObserver
{
    public function saved(SocialMediaCampaign $campaign): void
    {
        app(ClientJobSync::class)->sync($campaign);

        if ($campaign->wasChanged('status')) {
            ClientActivity::recordFor($campaign, 'campaign', 'Campaign '.$campaign->name.' marked '.str_replace('_', ' ', $campaign->status));
        }
    }
}
