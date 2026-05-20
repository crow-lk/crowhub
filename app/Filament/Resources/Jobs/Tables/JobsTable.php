<?php

namespace App\Filament\Resources\Jobs\Tables;

use App\Filament\Resources\Jobs\JobResource;
use App\Filament\Resources\MaintenanceContracts\MaintenanceContractResource;
use App\Filament\Resources\Projects\ProjectResource;
use App\Filament\Resources\SocialMediaCampaigns\SocialMediaCampaignResource;
use App\Models\ClientJob;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class JobsTable
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
                TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => JobResource::types()[$state] ?? $state)
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                TextColumn::make('invoice_total')
                    ->label('Invoiced')
                    ->money('lkr')
                    ->state(fn (ClientJob $record): float => $record->invoiceTotal()),
                TextColumn::make('paid_total')
                    ->label('Paid')
                    ->money('lkr')
                    ->state(fn (ClientJob $record): float => $record->paidTotal()),
                TextColumn::make('balance_due')
                    ->label('Balance')
                    ->money('lkr')
                    ->state(fn (ClientJob $record): float => $record->balanceDue()),
                TextColumn::make('latest_activity')
                    ->label('Latest activity')
                    ->state(fn (ClientJob $record): string => $record->activities()->latest('activity_date')->value('title') ?? '-'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options(JobResource::types()),
                Tables\Filters\SelectFilter::make('status')
                    ->options(JobResource::statuses()),
            ])
            ->recordActions([
                Action::make('openWork')
                    ->label('Open Work')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (ClientJob $record): ?string => self::workUrl($record)),
                ViewAction::make(),
                EditAction::make(),
            ]);
    }

    protected static function workUrl(ClientJob $record): ?string
    {
        return match ($record->type) {
            'project' => ProjectResource::getUrl('view', ['record' => $record->jobable_id]),
            'social_media_campaign' => SocialMediaCampaignResource::getUrl('view', ['record' => $record->jobable_id]),
            'maintenance_contract' => MaintenanceContractResource::getUrl('view', ['record' => $record->jobable_id]),
            default => null,
        };
    }
}
