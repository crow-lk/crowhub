<?php

namespace App\Filament\Resources\Clients\RelationManagers;

use App\Filament\Resources\SocialMediaCampaigns\SocialMediaCampaignResource;
use Filament\Actions\CreateAction;
use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class SocialMediaCampaignsRelationManager extends RelationManager
{
    protected static string $relationship = 'socialMediaCampaigns';

    public function table(Table $table): Table
    {
        return self::campaignsTable($table);
    }

    public static function campaignsTable(Table $table): Table
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
                Tables\Columns\TextColumn::make('platforms')
                    ->badge()
                    ->separator(', '),
                Tables\Columns\TextColumn::make('budget')
                    ->money('lkr')
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('start_date')
                    ->date()
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('end_date')
                    ->date()
                    ->placeholder('-'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->url(fn () => SocialMediaCampaignResource::getUrl('create')),
            ])
            ->recordActions([
                ViewAction::make()
                    ->url(fn ($record) => SocialMediaCampaignResource::getUrl('view', ['record' => $record])),
            ]);
    }
}
