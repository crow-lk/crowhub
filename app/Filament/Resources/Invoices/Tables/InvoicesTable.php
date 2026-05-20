<?php

namespace App\Filament\Resources\Invoices\Tables;

use App\Filament\Resources\Invoices\InvoiceResource;
use App\Models\Invoice;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Table;

class InvoicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('invoice_date', 'desc')
            ->columns(InvoiceResource::tableColumns())
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(InvoiceResource::statuses()),
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
