<?php

namespace App\Filament\Resources\Jobs;

use App\Filament\Resources\Jobs\Pages\EditJob;
use App\Filament\Resources\Jobs\Pages\ListJobs;
use App\Filament\Resources\Jobs\Pages\ViewJob;
use App\Filament\Resources\Jobs\RelationManagers\ActivitiesRelationManager;
use App\Filament\Resources\Jobs\RelationManagers\InvoicesRelationManager;
use App\Filament\Resources\Jobs\RelationManagers\PaymentsRelationManager;
use App\Filament\Resources\Jobs\Schemas\JobForm;
use App\Filament\Resources\Jobs\Schemas\JobInfolist;
use App\Filament\Resources\Jobs\Tables\JobsTable;
use App\Models\ClientJob;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class JobResource extends Resource
{
    protected static ?string $model = ClientJob::class;

    protected static string|UnitEnum|null $navigationGroup = 'Jobs';

    protected static ?int $navigationSort = 1;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $modelLabel = 'Job';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return JobForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return JobInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return JobsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            InvoicesRelationManager::class,
            PaymentsRelationManager::class,
            ActivitiesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListJobs::route('/'),
            'view' => ViewJob::route('/{record}'),
            'edit' => EditJob::route('/{record}/edit'),
        ];
    }

    public static function types(): array
    {
        return [
            'project' => 'Project',
            'social_media_campaign' => 'Social Media Campaign',
            'maintenance_contract' => 'Maintenance Contract',
        ];
    }

    public static function statuses(): array
    {
        return [
            'planned' => 'Planned',
            'active' => 'Active',
            'paused' => 'Paused',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
        ];
    }
}
