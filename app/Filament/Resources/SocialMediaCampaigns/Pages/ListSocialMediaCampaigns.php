<?php

namespace App\Filament\Resources\SocialMediaCampaigns\Pages;

use App\Filament\Resources\SocialMediaCampaigns\SocialMediaCampaignResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSocialMediaCampaigns extends ListRecords
{
    protected static string $resource = SocialMediaCampaignResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
