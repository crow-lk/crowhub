<?php

namespace App\Filament\Resources\Invoices;

use App\Filament\Resources\Invoices\Pages\CreateInvoice;
use App\Filament\Resources\Invoices\Pages\EditInvoice;
use App\Filament\Resources\Invoices\Pages\ListInvoices;
use App\Filament\Resources\Invoices\Pages\ViewInvoice;
use App\Filament\Resources\Invoices\RelationManagers\ItemsRelationManager;
use App\Filament\Resources\Invoices\RelationManagers\PaymentsRelationManager;
use App\Filament\Resources\Invoices\Schemas\InvoiceForm;
use App\Filament\Resources\Invoices\Schemas\InvoiceInfolist;
use App\Filament\Resources\Invoices\Tables\InvoicesTable;
use App\Models\Invoice;
use BackedEnum;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static string|UnitEnum|null $navigationGroup = 'Finance';

    protected static ?int $navigationSort = 5;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $recordTitleAttribute = 'invoice_no';

    public static function form(Schema $schema): Schema
    {
        return InvoiceForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return InvoiceInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InvoicesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            ItemsRelationManager::class,
            PaymentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListInvoices::route('/'),
            'create' => CreateInvoice::route('/create'),
            'view' => ViewInvoice::route('/{record}'),
            'edit' => EditInvoice::route('/{record}/edit'),
        ];
    }

    public static function statuses(): array
    {
        return [
            'draft' => 'Draft',
            'sent' => 'Sent',
            'partially_paid' => 'Partially Paid',
            'paid' => 'Paid',
            'overdue' => 'Overdue',
            'cancelled' => 'Cancelled',
        ];
    }

    public static function tableColumns(): array
    {
        return [
            TextColumn::make('invoice_no')
                ->label('Invoice #')
                ->searchable()
                ->sortable(),
            TextColumn::make('job.name')
                ->label('Job')
                ->searchable(),
            TextColumn::make('client.lead.name')
                ->label('Client')
                ->searchable(),
            TextColumn::make('invoice_date')
                ->date()
                ->sortable(),
            TextColumn::make('due_date')
                ->date()
                ->sortable(),
            TextColumn::make('total')
                ->money('lkr')
                ->sortable(),
            TextColumn::make('paid_amount')
                ->label('Paid')
                ->money('lkr')
                ->state(fn (Invoice $record): float => $record->paidAmount()),
            TextColumn::make('balance_due')
                ->label('Balance')
                ->money('lkr')
                ->state(fn (Invoice $record): float => $record->balanceDue()),
            TextColumn::make('status')
                ->badge()
                ->sortable(),
        ];
    }

    public static function paymentForm(Invoice $invoice): array
    {
        return [
            Forms\Components\TextInput::make('amount')
                ->numeric()
                ->prefix('LKR ')
                ->default($invoice->balanceDue())
                ->required()
                ->minValue(0.01),
            Forms\Components\DatePicker::make('paid_date')
                ->default(now())
                ->required(),
            Forms\Components\TextInput::make('method')
                ->maxLength(255),
            Forms\Components\Textarea::make('note')
                ->rows(3),
        ];
    }
}
