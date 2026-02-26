<?php

namespace App\Filament\Resources\Quotes\Pages;

use App\Filament\Resources\Quotes\QuoteResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewQuote extends ViewRecord
{
    protected static string $resource = QuoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('quotation')
                ->label('Quotation')
                ->icon('heroicon-o-document-text')
                ->url(fn () => route('admin.quotes.print', $this->record))
                ->openUrlInNewTab(),
            EditAction::make(),
        ];
    }
}
