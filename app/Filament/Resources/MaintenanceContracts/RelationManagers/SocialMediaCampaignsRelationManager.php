<?php

namespace App\Filament\Resources\MaintenanceContracts\RelationManagers;

use App\Filament\Resources\Clients\RelationManagers\SocialMediaCampaignsRelationManager as BaseSocialMediaCampaignsRelationManager;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class SocialMediaCampaignsRelationManager extends RelationManager
{
    protected static string $relationship = 'socialMediaCampaigns';

    public function table(Table $table): Table
    {
        return BaseSocialMediaCampaignsRelationManager::campaignsTable($table)
            ->headerActions([]);
    }
}
