<?php

namespace App\Filament\Resources\SocialMediaCampaigns\Schemas;

use Filament\Infolists;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SocialMediaCampaignInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Campaign')
                    ->columns(2)
                    ->components([
                        Infolists\Components\TextEntry::make('name'),
                        Infolists\Components\TextEntry::make('status')
                            ->badge(),
                        Infolists\Components\TextEntry::make('client.lead.name')
                            ->label('Client'),
                        Infolists\Components\TextEntry::make('maintenanceContract.id')
                            ->label('Maintenance contract')
                            ->formatStateUsing(fn ($state) => $state ? 'Contract #'.$state : '-')
                            ->placeholder('-'),
                        Infolists\Components\TextEntry::make('platforms')
                            ->badge()
                            ->separator(', ')
                            ->placeholder('-'),
                        Infolists\Components\TextEntry::make('budget')
                            ->money('lkr')
                            ->placeholder('-'),
                        Infolists\Components\TextEntry::make('paid_amount')
                            ->label('Paid')
                            ->money('lkr')
                            ->state(fn ($record): float => $record->paidAmount()),
                        Infolists\Components\TextEntry::make('balance_due')
                            ->label('Balance')
                            ->money('lkr')
                            ->state(fn ($record): float => $record->balanceDue()),
                        Infolists\Components\TextEntry::make('start_date')
                            ->date()
                            ->placeholder('-'),
                        Infolists\Components\TextEntry::make('end_date')
                            ->date()
                            ->placeholder('-'),
                        Infolists\Components\TextEntry::make('objective')
                            ->columnSpanFull()
                            ->placeholder('-'),
                    ]),
                Section::make('Content plan')
                    ->hidden(fn ($record) => blank($record->content_plan))
                    ->components([
                        Infolists\Components\TextEntry::make('content_plan')
                            ->columnSpanFull()
                            ->markdown(),
                    ]),
                Section::make('Results')
                    ->hidden(fn ($record) => blank($record->results_summary))
                    ->components([
                        Infolists\Components\TextEntry::make('results_summary')
                            ->columnSpanFull()
                            ->markdown(),
                    ]),
                Section::make('Notes')
                    ->hidden(fn ($record) => blank($record->notes))
                    ->components([
                        Infolists\Components\TextEntry::make('notes')
                            ->columnSpanFull()
                            ->markdown(),
                    ]),
            ]);
    }
}
