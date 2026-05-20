<?php

namespace App\Filament\Resources\Clients\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ActivitiesRelationManager extends RelationManager
{
    protected static string $relationship = 'activities';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->defaultSort('activity_date', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('activity_date')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('job.name')
                    ->label('Job')
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('title')
                    ->searchable(),
                Tables\Columns\TextColumn::make('description')
                    ->limit(80)
                    ->placeholder('-'),
            ]);
    }
}
