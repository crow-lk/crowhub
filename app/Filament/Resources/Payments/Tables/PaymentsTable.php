<?php

namespace App\Filament\Resources\Payments\Tables;

use App\Filament\Resources\Payments\PaymentResource;
use App\Models\Payment;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PaymentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('paid_date', 'desc')
            ->columns([
                TextColumn::make('lead.name')
                    ->label('Lead')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('quote.quote_no')
                    ->label('Quote #')
                    ->toggleable()
                    ->searchable(),
                TextColumn::make('invoice.invoice_no')
                    ->label('Invoice #')
                    ->toggleable()
                    ->searchable(),
                TextColumn::make('socialMediaCampaign.name')
                    ->label('Campaign')
                    ->toggleable()
                    ->searchable(),
                TextColumn::make('amount')
                    ->money('lkr')
                    ->sortable()
                    ->label('Quote/Invoice Total')
                    ->toggleable(),
                TextColumn::make('amount_to_pay')
                    ->money('lkr')
                    ->sortable()
                    ->label('Amount to Pay'),
                TextColumn::make('amount_paid')
                    ->money('lkr')
                    ->sortable()
                    ->label('Amount Paid'),
                TextColumn::make('to_pay')
                    ->money('lkr')
                    ->sortable()
                    ->label('Balance'),
                TextColumn::make('type')
                    ->badge()
                    ->sortable(),
                TextColumn::make('paid_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('method')
                    ->label('Method')
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options(PaymentResource::types()),
                Tables\Filters\Filter::make('paid_between')
                    ->form([
                        DatePicker::make('from')
                            ->label('From'),
                        DatePicker::make('until')
                            ->label('Until'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'] ?? null, fn ($q, $date) => $q->whereDate('paid_date', '>=', $date))
                            ->when($data['until'] ?? null, fn ($q, $date) => $q->whereDate('paid_date', '<=', $date));
                    }),
            ])
            ->recordActions([
                Action::make('Download PDF')
                    ->url(fn (Payment $record) => route('admin.payments.invoice', $record->id))
                    ->icon('heroicon-o-printer')
                    ->label('Invoice'),
                ViewAction::make(),
                EditAction::make(),
                Action::make('acceptPayment')
                    ->label('Accept Payment')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->form([
                        Forms\Components\TextInput::make('reference_number')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Transaction reference'),
                        Forms\Components\TextInput::make('amount_paid')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->prefix('LKR ')
                            ->default(fn (Payment $record) => $record->to_pay)
                            ->helperText('Amount received'),
                    ])
                    ->action(function (array $data, Payment $record) {
                        $currentBalance = $record->quote_id
                            ? $record->quote->balanceDue()
                            : ($record->invoice_id ? $record->invoice->balanceDue() : $record->amount_to_pay);

                        $oldAmountPaid = (float) $record->amount_paid;
                        $newAmountPaid = (float) $data['amount_paid'];

                        $record->update([
                            'amount_paid' => $data['amount_paid'],
                            'to_pay' => max(0, $currentBalance - ($newAmountPaid - $oldAmountPaid)),
                            'reference_number' => $data['reference_number'],
                        ]);

                        if ($record->invoice_id) {
                            $record->invoice->refreshTotals();
                        }
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
