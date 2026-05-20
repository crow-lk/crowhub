<?php

namespace App\Filament\Resources\Jobs\Schemas;

use App\Filament\Resources\Jobs\JobResource;
use App\Models\ClientJob;
use Filament\Infolists;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class JobInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Job')
                    ->columns(3)
                    ->components([
                        Infolists\Components\TextEntry::make('name'),
                        Infolists\Components\TextEntry::make('type')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => JobResource::types()[$state] ?? $state),
                        Infolists\Components\TextEntry::make('status')
                            ->badge(),
                        Infolists\Components\TextEntry::make('client.lead.name')
                            ->label('Client'),
                        Infolists\Components\TextEntry::make('invoice_total')
                            ->label('Invoiced')
                            ->money('lkr')
                            ->state(fn (ClientJob $record): float => $record->invoiceTotal()),
                        Infolists\Components\TextEntry::make('balance_due')
                            ->label('Balance')
                            ->money('lkr')
                            ->state(fn (ClientJob $record): float => $record->balanceDue()),
                        Infolists\Components\TextEntry::make('start_date')
                            ->date(),
                        Infolists\Components\TextEntry::make('end_date')
                            ->date()
                            ->placeholder('-'),
                        Infolists\Components\TextEntry::make('updated_at')
                            ->since()
                            ->label('Updated'),
                        Infolists\Components\TextEntry::make('description')
                            ->columnSpanFull()
                            ->placeholder('-'),
                        Infolists\Components\TextEntry::make('notes')
                            ->columnSpanFull()
                            ->placeholder('-'),
                    ]),
            ]);
    }
}
