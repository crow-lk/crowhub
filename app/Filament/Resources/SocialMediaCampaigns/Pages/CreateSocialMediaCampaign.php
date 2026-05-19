<?php

namespace App\Filament\Resources\SocialMediaCampaigns\Pages;

use App\Filament\Resources\SocialMediaCampaigns\SocialMediaCampaignResource;
use App\Models\Client;
use Filament\Resources\Pages\CreateRecord;

class CreateSocialMediaCampaign extends CreateRecord
{
    protected static string $resource = SocialMediaCampaignResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (empty($data['lead_id']) && ! empty($data['client_id'])) {
            $data['lead_id'] = Client::find($data['client_id'])?->lead_id;
        }

        return $data;
    }
}
