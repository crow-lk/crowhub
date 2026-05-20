<?php

namespace App\Filament\Resources\Invoices\Schemas;

use App\Filament\Resources\Invoices\InvoiceResource;
use App\Models\ClientJob;
use Filament\Forms;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class InvoiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Invoice')
                    ->columns(2)
                    ->components([
                        Forms\Components\Select::make('client_job_id')
                            ->label('Job')
                            ->relationship('job', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (callable $set, ?int $state): void {
                                $job = $state ? ClientJob::find($state) : null;
                                $set('client_id', $job?->client_id);
                                $set('lead_id', $job?->lead_id);
                            }),
                        Forms\Components\Hidden::make('client_id'),
                        Forms\Components\Hidden::make('lead_id'),
                        Forms\Components\TextInput::make('invoice_no')
                            ->label('Invoice #')
                            ->disabled()
                            ->dehydrated(false),
                        Forms\Components\DatePicker::make('billing_month')
                            ->label('Billing month'),
                        Forms\Components\DatePicker::make('invoice_date')
                            ->default(now())
                            ->required(),
                        Forms\Components\DatePicker::make('due_date')
                            ->default(now()->addDays(7))
                            ->required(),
                        Forms\Components\Select::make('status')
                            ->options(InvoiceResource::statuses())
                            ->default('draft')
                            ->required(),
                        Forms\Components\TextInput::make('discount')
                            ->numeric()
                            ->prefix('LKR ')
                            ->default(0)
                            ->minValue(0),
                        Forms\Components\TextInput::make('tax')
                            ->numeric()
                            ->prefix('LKR ')
                            ->default(0)
                            ->minValue(0),
                        Forms\Components\Textarea::make('notes')
                            ->columnSpanFull()
                            ->rows(3),
                    ]),
            ]);
    }
}
