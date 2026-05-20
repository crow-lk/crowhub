<?php

namespace App\Filament\Resources\SocialMediaCampaigns\Tables;

use App\Filament\Resources\SocialMediaCampaigns\SocialMediaCampaignResource;
use App\Models\SocialMediaCampaign;
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

class SocialMediaCampaignsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('updated_at', 'desc')
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('client.lead.name')
                    ->label('Client')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                TextColumn::make('platforms')
                    ->badge()
                    ->separator(', ')
                    ->toggleable(),
                TextColumn::make('budget')
                    ->money('lkr')
                    ->placeholder('-')
                    ->sortable(),
                TextColumn::make('paid_amount')
                    ->label('Paid')
                    ->money('lkr')
                    ->state(fn (SocialMediaCampaign $record): float => $record->paidAmount()),
                TextColumn::make('balance_due')
                    ->label('Balance')
                    ->money('lkr')
                    ->state(fn (SocialMediaCampaign $record): float => $record->balanceDue()),
                TextColumn::make('start_date')
                    ->date()
                    ->placeholder('-')
                    ->sortable(),
                TextColumn::make('end_date')
                    ->date()
                    ->placeholder('-')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(SocialMediaCampaignResource::statuses()),
                Tables\Filters\SelectFilter::make('platforms')
                    ->options(SocialMediaCampaignResource::platforms())
                    ->query(function ($query, array $data) {
                        if (blank($data['value'] ?? null)) {
                            return $query;
                        }

                        return $query->whereJsonContains('platforms', $data['value']);
                    }),
            ])
            ->recordActions([
                Action::make('createInvoice')
                    ->label('Create Invoice')
                    ->icon('heroicon-o-document-plus')
                    ->form(fn (SocialMediaCampaign $record): array => [
                        Forms\Components\TextInput::make('description')
                            ->default($record->name)
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('amount')
                            ->numeric()
                            ->prefix('LKR ')
                            ->default($record->balanceDue() > 0 ? $record->balanceDue() : $record->budget)
                            ->required()
                            ->minValue(0.01),
                        Forms\Components\DatePicker::make('billing_month')
                            ->label('Billing month'),
                        Forms\Components\DatePicker::make('invoice_date')
                            ->default(now())
                            ->required(),
                        Forms\Components\DatePicker::make('due_date')
                            ->default(now()->addDays(7))
                            ->required(),
                        Forms\Components\Textarea::make('notes')
                            ->rows(3),
                    ])
                    ->action(function (SocialMediaCampaign $record, array $data): void {
                        $invoice = app(InvoiceCreator::class)->forCampaign($record, $data);

                        Notification::make()
                            ->title('Campaign invoice created')
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
