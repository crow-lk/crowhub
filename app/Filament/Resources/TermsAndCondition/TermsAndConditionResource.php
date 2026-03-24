<?php

namespace App\Filament\Resources\TermsAndCondition;

use App\Filament\Resources\TermsAndCondition\Pages\CreateTermsAndCondition;
use App\Filament\Resources\TermsAndCondition\Pages\EditTermsAndCondition;
use App\Filament\Resources\TermsAndCondition\Pages\ListTermsAndConditions;
use App\Filament\Resources\TermsAndCondition\Pages\ViewTermsAndCondition;
use App\Filament\Resources\TermsAndCondition\Schemas\TermsAndConditionForm;
use App\Filament\Resources\TermsAndCondition\Schemas\TermsAndConditionInfolist;
use App\Filament\Resources\TermsAndCondition\Tables\TermsAndConditionsTable;
use App\Models\TermsAndCondition;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class TermsAndConditionResource extends Resource
{
    protected static ?string $model = TermsAndCondition::class;

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?int $navigationSort = 100;

    public static function form(Schema $schema): Schema
    {
        return TermsAndConditionForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return TermsAndConditionInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TermsAndConditionsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTermsAndConditions::route('/'),
            'create' => CreateTermsAndCondition::route('/create'),
            'view' => ViewTermsAndCondition::route('/{record}'),
            'edit' => EditTermsAndCondition::route('/{record}/edit'),
        ];
    }
}
