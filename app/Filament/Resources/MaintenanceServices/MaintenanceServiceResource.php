<?php

namespace App\Filament\Resources\MaintenanceServices;

use App\Filament\Resources\MaintenanceServices\Pages\CreateMaintenanceService;
use App\Filament\Resources\MaintenanceServices\Pages\EditMaintenanceService;
use App\Filament\Resources\MaintenanceServices\Pages\ListMaintenanceServices;
use App\Filament\Resources\MaintenanceServices\Schemas\MaintenanceServiceForm;
use App\Filament\Resources\MaintenanceServices\Tables\MaintenanceServicesTable;
use App\Models\MaintenanceService;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class MaintenanceServiceResource extends Resource
{
    protected static ?string $model = MaintenanceService::class;

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 30;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedWrenchScrewdriver;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return MaintenanceServiceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MaintenanceServicesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMaintenanceServices::route('/'),
            'create' => CreateMaintenanceService::route('/create'),
            'edit' => EditMaintenanceService::route('/{record}/edit'),
        ];
    }
}
