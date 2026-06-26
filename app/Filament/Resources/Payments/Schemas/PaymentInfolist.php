<?php

namespace App\Filament\Resources\Payments\Schemas;

use Filament\Infolists;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PaymentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Details')
                    ->columns(2)
                    ->components([
                        Infolists\Components\TextEntry::make('lead.name')
                            ->label('Lead'),
                        Infolists\Components\TextEntry::make('quote.quote_no')
                            ->label('Quote #')
                            ->placeholder('-'),
                        Infolists\Components\TextEntry::make('invoice.invoice_no')
                            ->label('Invoice #')
                            ->placeholder('-'),
                        Infolists\Components\TextEntry::make('amount')
                            ->money('lkr')
                            ->label('Amount'),
                        Infolists\Components\TextEntry::make('amount_to_pay')
                            ->money('lkr')
                            ->label('Amount to Pay'),
                        Infolists\Components\TextEntry::make('amount_paid')
                            ->money('lkr')
                            ->label('Amount Paid'),
                        Infolists\Components\TextEntry::make('to_pay')
                            ->money('lkr')
                            ->label('Balance'),
                        Infolists\Components\TextEntry::make('type')
                            ->badge()
                            ->label('Type'),
                        Infolists\Components\TextEntry::make('paid_date')
                            ->date()
                            ->label('Paid on'),
                        Infolists\Components\TextEntry::make('method')
                            ->label('Method')
                            ->placeholder('-'),
                    ]),
                Section::make('Notes')
                    ->hidden(fn ($record) => blank($record->note))
                    ->components([
                        Infolists\Components\TextEntry::make('note')
                            ->columnSpanFull()
                            ->markdown(),
                    ]),
            ]);
    }
}
