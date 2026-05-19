<?php

namespace App\Filament\Resources\Clients\RelationManagers;

use App\Filament\Resources\Projects\ProjectResource;
use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ProjectsRelationManager extends RelationManager
{
    protected static string $relationship = 'projects';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('uninvoiced_total')
                    ->label('Uninvoiced')
                    ->money('lkr')
                    ->state(fn ($record): float => (float) $record->tasks()
                        ->where('status', 'done')
                        ->whereNull('invoiced_at')
                        ->sum('amount')),
                Tables\Columns\TextColumn::make('updated_at')
                    ->since()
                    ->label('Updated'),
            ])
            ->recordActions([
                ViewAction::make()
                    ->url(fn ($record) => ProjectResource::getUrl('view', ['record' => $record])),
            ]);
    }
}
