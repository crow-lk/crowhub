<?php

namespace App\Filament\Resources\Clients\RelationManagers;

use App\Filament\Resources\Jobs\JobResource;
use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class JobsRelationManager extends RelationManager
{
    protected static string $relationship = 'jobs';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('invoice_total')
                    ->label('Invoiced')
                    ->money('lkr')
                    ->state(fn ($record): float => $record->invoiceTotal()),
                Tables\Columns\TextColumn::make('balance_due')
                    ->label('Balance')
                    ->money('lkr')
                    ->state(fn ($record): float => $record->balanceDue()),
            ])
            ->recordActions([
                ViewAction::make()
                    ->url(fn ($record) => JobResource::getUrl('view', ['record' => $record])),
            ]);
    }
}
