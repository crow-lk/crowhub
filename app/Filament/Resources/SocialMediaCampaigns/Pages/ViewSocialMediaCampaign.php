<?php

namespace App\Filament\Resources\SocialMediaCampaigns\Pages;

use App\Filament\Resources\SocialMediaCampaigns\SocialMediaCampaignResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewSocialMediaCampaign extends ViewRecord
{
    protected static string $resource = SocialMediaCampaignResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
