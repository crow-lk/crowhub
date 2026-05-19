<?php

namespace App\Filament\Resources\Projects\RelationManagers;

use App\Models\Project;
use App\Models\ProjectInvoice;
use App\Models\ProjectTask;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;

class InvoicesRelationManager extends RelationManager
{
    protected static string $relationship = 'invoices';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Forms\Components\TextInput::make('invoice_no')
                    ->label('Invoice #')
                    ->disabled()
                    ->dehydrated(false),
                Forms\Components\DatePicker::make('invoice_month')
                    ->label('Invoice month')
                    ->default(now()->startOfMonth())
                    ->required(),
                Forms\Components\TextInput::make('amount')
                    ->numeric()
                    ->prefix('LKR ')
                    ->required()
                    ->minValue(0.01),
                Forms\Components\Select::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'sent' => 'Sent',
                        'paid' => 'Paid',
                        'overdue' => 'Overdue',
                        'cancelled' => 'Cancelled',
                    ])
                    ->default('draft')
                    ->required(),
                Forms\Components\DatePicker::make('issued_date')
                    ->default(now())
                    ->required(),
                Forms\Components\DatePicker::make('due_date')
                    ->required(),
                Forms\Components\DatePicker::make('paid_date')
                    ->label('Paid on'),
                Forms\Components\Textarea::make('notes')
                    ->columnSpanFull()
                    ->rows(3),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('invoice_no')
            ->columns([
                Tables\Columns\TextColumn::make('invoice_no')
                    ->label('Invoice #')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('invoice_month')
                    ->date('M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->money('lkr')
                    ->sortable(),
                Tables\Columns\TextColumn::make('paid_amount')
                    ->label('Paid')
                    ->money('lkr')
                    ->state(fn (ProjectInvoice $record): float => $record->paidAmount()),
                Tables\Columns\TextColumn::make('balance_due')
                    ->label('Balance')
                    ->money('lkr')
                    ->state(fn (ProjectInvoice $record): float => $record->balanceDue()),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('due_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('paid_date')
                    ->date()
                    ->placeholder('-'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Create Month-End Invoice')
                    ->form(function (): array {
                        /** @var Project $project */
                        $project = $this->getOwnerRecord();

                        return [
                            Forms\Components\DatePicker::make('invoice_month')
                                ->label('Invoice month')
                                ->default(now()->startOfMonth())
                                ->live()
                                ->required(),
                            Forms\Components\CheckboxList::make('task_ids')
                                ->label('Completed uninvoiced tasks')
                                ->options(function (callable $get) use ($project): array {
                                    $month = Carbon::parse($get('invoice_month') ?? now())->startOfMonth();

                                    return $project->tasks()
                                        ->where('status', 'done')
                                        ->whereNull('invoiced_at')
                                        ->whereBetween('completed_at', [$month, $month->copy()->endOfMonth()])
                                        ->orderBy('completed_at')
                                        ->orderBy('title')
                                        ->get()
                                        ->mapWithKeys(fn (ProjectTask $task): array => [
                                            $task->id => $task->title.' - LKR '.number_format((float) $task->amount, 2),
                                        ])
                                        ->toArray();
                                })
                                ->required()
                                ->columns(1)
                                ->helperText('Only completed, uninvoiced tasks from the selected month are shown. Set task amounts before creating the invoice.'),
                            Forms\Components\DatePicker::make('issued_date')
                                ->default(now())
                                ->required(),
                            Forms\Components\DatePicker::make('due_date')
                                ->default(now()->addDays(7))
                                ->required(),
                            Forms\Components\Textarea::make('notes')
                                ->rows(3),
                        ];
                    })
                    ->action(function (array $data): void {
                        /** @var Project $project */
                        $project = $this->getOwnerRecord();
                        $tasks = $project->tasks()
                            ->whereIn('id', $data['task_ids'])
                            ->where('status', 'done')
                            ->whereNull('invoiced_at')
                            ->get();

                        if ($tasks->isEmpty()) {
                            Notification::make()
                                ->title('No billable tasks selected')
                                ->warning()
                                ->send();

                            return;
                        }

                        $invoice = $project->invoices()->create([
                            'invoice_month' => $data['invoice_month'],
                            'amount' => $tasks->sum(fn (ProjectTask $task): float => (float) $task->amount),
                            'status' => 'draft',
                            'issued_date' => $data['issued_date'],
                            'due_date' => $data['due_date'],
                            'notes' => $data['notes'] ?? null,
                        ]);

                        foreach ($tasks as $task) {
                            $invoice->items()->create([
                                'project_task_id' => $task->id,
                                'description' => $task->title,
                                'amount' => $task->amount,
                            ]);

                            $task->update([
                                'status' => 'invoiced',
                                'invoiced_at' => now(),
                            ]);
                        }
                    }),
            ])
            ->recordActions([
                Action::make('invoicePdf')
                    ->label('Invoice')
                    ->icon('heroicon-o-printer')
                    ->url(fn (ProjectInvoice $record) => route('admin.project-invoices.show', $record))
                    ->openUrlInNewTab(),
                Action::make('markSent')
                    ->label('Mark sent')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('warning')
                    ->visible(fn ($record) => $record->status === 'draft')
                    ->action(fn ($record) => $record->update(['status' => 'sent'])),
                Action::make('markPaid')
                    ->label('Mark paid without record')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn ($record) => in_array($record->status, ['draft', 'sent', 'overdue'], true))
                    ->action(fn ($record) => $record->update([
                        'status' => 'paid',
                        'paid_date' => now(),
                    ])),
                Action::make('recordPayment')
                    ->label('Record Payment')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->visible(fn (ProjectInvoice $record) => $record->status !== 'cancelled' && $record->balanceDue() > 0)
                    ->form(fn (ProjectInvoice $record): array => [
                        Forms\Components\TextInput::make('amount')
                            ->numeric()
                            ->prefix('LKR ')
                            ->default($record->balanceDue())
                            ->required()
                            ->minValue(0.01),
                        Forms\Components\DatePicker::make('paid_date')
                            ->default(now())
                            ->required(),
                        Forms\Components\TextInput::make('method')
                            ->maxLength(255),
                        Forms\Components\Textarea::make('note')
                            ->rows(3),
                    ])
                    ->action(function (ProjectInvoice $record, array $data): void {
                        $record->payments()->create($data);
                        $record->refresh();

                        if ($record->balanceDue() <= 0) {
                            $record->update([
                                'status' => 'paid',
                                'paid_date' => $data['paid_date'],
                            ]);
                        } elseif ($record->status === 'draft') {
                            $record->update(['status' => 'sent']);
                        }
                    }),
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
