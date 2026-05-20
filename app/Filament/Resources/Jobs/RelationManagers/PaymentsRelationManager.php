<?php

namespace App\Filament\Resources\Jobs\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('invoice.invoice_no')
                    ->label('Invoice')
                    ->searchable(),
                Tables\Columns\TextColumn::make('amount')
                    ->money('lkr')
                    ->sortable(),
                Tables\Columns\TextColumn::make('paid_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('method')
                    ->placeholder('-'),
            ]);
    }
}
