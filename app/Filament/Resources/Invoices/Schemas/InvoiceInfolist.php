<?php

namespace App\Filament\Resources\Invoices\Schemas;

use App\Models\Invoice;
use Filament\Infolists;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class InvoiceInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Invoice')
                    ->columns(3)
                    ->components([
                        Infolists\Components\TextEntry::make('invoice_no')
                            ->label('Invoice #'),
                        Infolists\Components\TextEntry::make('job.name')
                            ->label('Job'),
                        Infolists\Components\TextEntry::make('client.lead.name')
                            ->label('Client'),
                        Infolists\Components\TextEntry::make('status')
                            ->badge(),
                        Infolists\Components\TextEntry::make('invoice_date')
                            ->date(),
                        Infolists\Components\TextEntry::make('due_date')
                            ->date(),
                        Infolists\Components\TextEntry::make('subtotal')
                            ->money('lkr'),
                        Infolists\Components\TextEntry::make('total')
                            ->money('lkr'),
                        Infolists\Components\TextEntry::make('balance_due')
                            ->label('Balance')
                            ->money('lkr')
                            ->state(fn (Invoice $record): float => $record->balanceDue()),
                        Infolists\Components\TextEntry::make('notes')
                            ->columnSpanFull()
                            ->placeholder('-'),
                    ]),
            ]);
    }
}
