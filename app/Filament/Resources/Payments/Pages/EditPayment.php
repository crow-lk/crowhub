<?php

namespace App\Filament\Resources\Payments\Pages;

use App\Filament\Resources\Payments\PaymentResource;
use App\Models\Payment;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Resources\Pages\EditRecord;

class EditPayment extends EditRecord
{
    protected static string $resource = PaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
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
        ];
    }
}
