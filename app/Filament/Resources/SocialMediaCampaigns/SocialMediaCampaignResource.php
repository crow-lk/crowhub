<?php

namespace App\Filament\Resources\SocialMediaCampaigns;

use App\Filament\Resources\SocialMediaCampaigns\Pages\CreateSocialMediaCampaign;
use App\Filament\Resources\SocialMediaCampaigns\Pages\EditSocialMediaCampaign;
use App\Filament\Resources\SocialMediaCampaigns\Pages\ListSocialMediaCampaigns;
use App\Filament\Resources\SocialMediaCampaigns\Pages\ViewSocialMediaCampaign;
use App\Filament\Resources\SocialMediaCampaigns\RelationManagers\PaymentsRelationManager;
use App\Filament\Resources\SocialMediaCampaigns\Schemas\SocialMediaCampaignForm;
use App\Filament\Resources\SocialMediaCampaigns\Schemas\SocialMediaCampaignInfolist;
use App\Filament\Resources\SocialMediaCampaigns\Tables\SocialMediaCampaignsTable;
use App\Models\SocialMediaCampaign;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class SocialMediaCampaignResource extends Resource
{
    protected static ?string $model = SocialMediaCampaign::class;

    protected static string|UnitEnum|null $navigationGroup = 'Jobs';

    protected static ?int $navigationSort = 30;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMegaphone;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return SocialMediaCampaignForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return SocialMediaCampaignInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SocialMediaCampaignsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            PaymentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSocialMediaCampaigns::route('/'),
            'create' => CreateSocialMediaCampaign::route('/create'),
            'view' => ViewSocialMediaCampaign::route('/{record}'),
            'edit' => EditSocialMediaCampaign::route('/{record}/edit'),
        ];
    }

    public static function statuses(): array
    {
        return [
            'planned' => 'Planned',
            'active' => 'Active',
            'completed' => 'Completed',
            'paused' => 'Paused',
            'cancelled' => 'Cancelled',
        ];
    }

    public static function platforms(): array
    {
        return [
            'facebook' => 'Facebook',
            'instagram' => 'Instagram',
            'tiktok' => 'TikTok',
            'linkedin' => 'LinkedIn',
            'youtube' => 'YouTube',
            'x' => 'X',
            'google_ads' => 'Google Ads',
        ];
    }
}
