<?php

namespace App\Filament\Resources\Invoices\RelationManagers;

use App\Models\Invoice;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Forms\Components\TextInput::make('amount')
                    ->numeric()
                    ->prefix('LKR ')
                    ->required()
                    ->minValue(0.01),
                Forms\Components\DatePicker::make('paid_date')
                    ->default(now())
                    ->required(),
                Forms\Components\TextInput::make('method')
                    ->maxLength(255),
                Forms\Components\Textarea::make('note')
                    ->columnSpanFull()
                    ->rows(3),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('amount')
                    ->money('lkr')
                    ->sortable(),
                Tables\Columns\TextColumn::make('paid_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('method')
                    ->placeholder('-'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        /** @var Invoice $invoice */
                        $invoice = $this->getOwnerRecord();

                        $data['lead_id'] = $invoice->lead_id;
                        $data['type'] = 'other';

                        return $data;
                    }),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
