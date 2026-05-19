<?php

namespace App\Filament\Resources\MaintenanceContracts;

use App\Filament\Resources\MaintenanceContracts\Pages\CreateMaintenanceContract;
use App\Filament\Resources\MaintenanceContracts\Pages\EditMaintenanceContract;
use App\Filament\Resources\MaintenanceContracts\Pages\ListMaintenanceContracts;
use App\Filament\Resources\MaintenanceContracts\Pages\ViewMaintenanceContract;
use App\Filament\Resources\MaintenanceContracts\RelationManagers\PaymentsRelationManager;
use App\Filament\Resources\MaintenanceContracts\RelationManagers\SocialMediaCampaignsRelationManager;
use App\Filament\Resources\MaintenanceContracts\Schemas\MaintenanceContractForm;
use App\Filament\Resources\MaintenanceContracts\Schemas\MaintenanceContractInfolist;
use App\Filament\Resources\MaintenanceContracts\Tables\MaintenanceContractsTable;
use App\Models\MaintenanceContract;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class MaintenanceContractResource extends Resource
{
    protected static ?string $model = MaintenanceContract::class;

    protected static string|UnitEnum|null $navigationGroup = 'Jobs';

    protected static ?int $navigationSort = 20;

    protected static ?string $recordTitleAttribute = 'lead.name';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return MaintenanceContractForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return MaintenanceContractInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MaintenanceContractsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            PaymentsRelationManager::class,
            SocialMediaCampaignsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMaintenanceContracts::route('/'),
            'create' => CreateMaintenanceContract::route('/create'),
            'view' => ViewMaintenanceContract::route('/{record}'),
            'edit' => EditMaintenanceContract::route('/{record}/edit'),
        ];
    }

    public static function statuses(): array
    {
        return [
            'active' => 'Active',
            'paused' => 'Paused',
            'cancelled' => 'Cancelled',
        ];
    }
}
