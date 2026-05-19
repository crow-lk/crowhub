<?php

namespace App\Filament\Resources\SocialMediaCampaigns\Pages;

use App\Filament\Resources\SocialMediaCampaigns\SocialMediaCampaignResource;
use App\Models\Client;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditSocialMediaCampaign extends EditRecord
{
    protected static string $resource = SocialMediaCampaignResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (empty($data['lead_id']) && ! empty($data['client_id'])) {
            $data['lead_id'] = Client::find($data['client_id'])?->lead_id;
        }

        return $data;
    }
}
