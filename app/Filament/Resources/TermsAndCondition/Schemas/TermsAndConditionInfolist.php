<?php

namespace App\Filament\Resources\TermsAndCondition\Schemas;

use Filament\Infolists\Components;
use Filament\Infolists\Infolist;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TermsAndConditionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Term Details')
                    ->schema([
                        Components\TextEntry::make('sort_order')
                            ->label('Sort Order'),
                    ]),
                Section::make('Content')
                    ->schema([
                        Components\TextEntry::make('content')
                            ->label('Main Term')
                            ->html()
                            ->prose(),
                        Components\RepeatableEntry::make('secondary_terms')
                            ->schema([
                                Components\TextEntry::make('content')
                                    ->label('Content')
                                    ->html()
                                    ->prose(),
                            ]),
                    ]),
                Section::make('Status')
                    ->columns(2)
                    ->schema([
                        Components\TextEntry::make('is_active')
                            ->label('Status')
                            ->badge()
                            ->color(fn(bool $state): string => $state ? 'success' : 'danger')
                            ->formatStateUsing(fn(bool $state): string => $state ? 'Active' : 'Inactive'),
                        Components\TextEntry::make('created_at')
                            ->label('Created At')
                            ->dateTime(),
                    ]),
            ]);
    }
}
