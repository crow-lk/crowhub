<?php

namespace App\Filament\Resources\Projects\RelationManagers;

use App\Filament\Resources\Invoices\InvoiceResource;
use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class UnifiedInvoicesRelationManager extends RelationManager
{
    protected static string $relationship = 'unifiedInvoices';

    protected static ?string $title = 'Unified Invoices';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('invoice_no')
            ->columns(InvoiceResource::tableColumns())
            ->recordActions([
                ViewAction::make()
                    ->url(fn ($record) => InvoiceResource::getUrl('view', ['record' => $record])),
            ]);
    }
}
