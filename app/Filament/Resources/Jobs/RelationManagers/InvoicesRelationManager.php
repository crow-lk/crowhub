<?php

namespace App\Filament\Resources\Jobs\RelationManagers;

use App\Filament\Resources\Invoices\InvoiceResource;
use App\Models\ClientJob;
use App\Models\Invoice;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class InvoicesRelationManager extends RelationManager
{
    protected static string $relationship = 'invoices';

    public function form(Schema $schema): Schema
    {
        return InvoiceResource::form($schema);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('invoice_no')
            ->columns(InvoiceResource::tableColumns())
            ->headerActions([
                CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        /** @var ClientJob $job */
                        $job = $this->getOwnerRecord();

                        $data['client_id'] = $job->client_id;
                        $data['lead_id'] = $job->lead_id;

                        return $data;
                    }),
            ])
            ->recordActions([
                Action::make('markSent')
                    ->label('Mark sent')
                    ->icon('heroicon-o-paper-airplane')
                    ->visible(fn (Invoice $record): bool => $record->status === 'draft')
                    ->action(fn (Invoice $record) => $record->update(['status' => 'sent'])),
                Action::make('recordPayment')
                    ->label('Record Payment')
                    ->icon('heroicon-o-banknotes')
                    ->visible(fn (Invoice $record): bool => $record->status !== 'cancelled' && $record->balanceDue() > 0)
                    ->form(fn (Invoice $record): array => InvoiceResource::paymentForm($record))
                    ->action(function (Invoice $record, array $data): void {
                        $record->payments()->create([
                            ...$data,
                            'lead_id' => $record->lead_id,
                            'type' => 'other',
                        ]);

                        Notification::make()->title('Payment recorded')->success()->send();
                    }),
                ViewAction::make()
                    ->url(fn (Invoice $record): string => InvoiceResource::getUrl('view', ['record' => $record])),
            ]);
    }
}
