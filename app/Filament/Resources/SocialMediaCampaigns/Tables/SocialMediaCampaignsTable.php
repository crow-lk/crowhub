<?php

namespace App\Filament\Resources\SocialMediaCampaigns\Tables;

use App\Filament\Resources\SocialMediaCampaigns\SocialMediaCampaignResource;
use App\Models\SocialMediaCampaign;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SocialMediaCampaignsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('updated_at', 'desc')
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('client.lead.name')
                    ->label('Client')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                TextColumn::make('platforms')
                    ->badge()
                    ->separator(', ')
                    ->toggleable(),
                TextColumn::make('budget')
                    ->money('lkr')
                    ->placeholder('-')
                    ->sortable(),
                TextColumn::make('paid_amount')
                    ->label('Paid')
                    ->money('lkr')
                    ->state(fn (SocialMediaCampaign $record): float => $record->paidAmount()),
                TextColumn::make('balance_due')
                    ->label('Balance')
                    ->money('lkr')
                    ->state(fn (SocialMediaCampaign $record): float => $record->balanceDue()),
                TextColumn::make('start_date')
                    ->date()
                    ->placeholder('-')
                    ->sortable(),
                TextColumn::make('end_date')
                    ->date()
                    ->placeholder('-')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(SocialMediaCampaignResource::statuses()),
                Tables\Filters\SelectFilter::make('platforms')
                    ->options(SocialMediaCampaignResource::platforms())
                    ->query(function ($query, array $data) {
                        if (blank($data['value'] ?? null)) {
                            return $query;
                        }

                        return $query->whereJsonContains('platforms', $data['value']);
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
