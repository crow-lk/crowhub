<?php

namespace App\Filament\Resources\TermsAndCondition\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\BooleanColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TermsAndConditionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('content')
                    ->label('Content')
                    ->limit(100)
                    ->html(),
                BooleanColumn::make('is_active')
                    ->label('Active')
                    ->sortable(),
                TextColumn::make('sort_order')
                    ->label('Order')
                    ->sortable()
                    ->width('80px'),
            ])
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                // Bulk actions can be added here
            ])
            ->paginated([10, 25, 50]);
    }
}
