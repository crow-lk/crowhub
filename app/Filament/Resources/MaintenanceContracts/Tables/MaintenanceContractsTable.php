<?php

namespace App\Filament\Resources\MaintenanceContracts\Tables;

use App\Filament\Resources\MaintenanceContracts\MaintenanceContractResource;
use App\Models\MaintenanceContract;
use App\Services\Billing\InvoiceCreator;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MaintenanceContractsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('lead.name')
                    ->label('Lead')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('services_list')
                    ->label('Services')
                    ->state(fn ($record): string => $record->services->pluck('name')->join(', '))
                    ->badge()
                    ->toggleable(),
                TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                TextColumn::make('monthly_fee')
                    ->money('lkr')
                    ->label('Monthly fee')
                    ->sortable(),
                TextColumn::make('billing_day')
                    ->label('Billing day'),
                TextColumn::make('next_due_date')
                    ->label('Next due')
                    ->state(fn ($record) => optional($record->statusInfo()['next_due_date'])->toFormattedDateString())
                    ->badge()
                    ->color(fn ($record) => $record->statusInfo()['is_overdue'] ? 'danger' : ($record->statusInfo()['is_due_soon'] ? 'warning' : 'success')),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(MaintenanceContractResource::statuses()),
            ])
            ->recordActions([
                Action::make('createMonthlyInvoice')
                    ->label('Create Monthly Invoice')
                    ->icon('heroicon-o-document-plus')
                    ->visible(fn (MaintenanceContract $record): bool => $record->status === 'active')
                    ->form([
                        Forms\Components\DatePicker::make('billing_month')
                            ->label('Billing month')
                            ->default(now()->startOfMonth())
                            ->required(),
                        Forms\Components\DatePicker::make('invoice_date')
                            ->default(now())
                            ->required(),
                        Forms\Components\DatePicker::make('due_date')
                            ->default(now()->addDays(7))
                            ->required(),
                        Forms\Components\Textarea::make('notes')
                            ->rows(3),
                    ])
                    ->action(function (MaintenanceContract $record, array $data): void {
                        $invoice = app(InvoiceCreator::class)->forMaintenanceMonth($record, $data);

                        Notification::make()
                            ->title('Maintenance invoice created')
                            ->body($invoice->invoice_no)
                            ->success()
                            ->send();
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
