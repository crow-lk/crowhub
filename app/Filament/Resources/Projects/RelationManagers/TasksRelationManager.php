<?php

namespace App\Filament\Resources\Projects\RelationManagers;

use App\Models\Project;
use App\Models\ProjectTask;
use App\Services\Billing\InvoiceCreator;
use App\Services\GitHub\GitHubProjectSync;
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

class TasksRelationManager extends RelationManager
{
    protected static string $relationship = 'tasks';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('github_task_url')
                    ->label('GitHub task URL')
                    ->url()
                    ->maxLength(255),
                Forms\Components\Select::make('status')
                    ->options([
                        'todo' => 'Todo',
                        'in_progress' => 'In Progress',
                        'done' => 'Done',
                        'invoiced' => 'Invoiced',
                        'cancelled' => 'Cancelled',
                    ])
                    ->default('todo')
                    ->required(),
                Forms\Components\TextInput::make('amount')
                    ->numeric()
                    ->prefix('LKR ')
                    ->default(0)
                    ->required()
                    ->minValue(0),
                Forms\Components\DatePicker::make('completed_at')
                    ->label('Completed on'),
                Forms\Components\DatePicker::make('invoiced_at')
                    ->label('Invoiced on')
                    ->disabled()
                    ->dehydrated(),
                Forms\Components\Textarea::make('description')
                    ->columnSpanFull()
                    ->rows(3),
                Forms\Components\Textarea::make('notes')
                    ->columnSpanFull()
                    ->rows(3),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('github_status')
                    ->label('GitHub status')
                    ->badge()
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('github_assignees')
                    ->label('Assignees')
                    ->state(fn ($record): string => implode(', ', $record->github_assignees ?? []))
                    ->placeholder('-')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('amount')
                    ->money('lkr')
                    ->sortable(),
                Tables\Columns\TextColumn::make('completed_at')
                    ->date()
                    ->placeholder('-')
                    ->sortable(),
                Tables\Columns\TextColumn::make('invoiced_at')
                    ->date()
                    ->placeholder('-'),
            ])
            ->filters([
                Tables\Filters\Filter::make('uninvoiced')
                    ->label('Non-invoiced done tasks')
                    ->query(fn ($query) => $query
                        ->where('status', 'done')
                        ->whereNull('invoiced_at')),
                Tables\Filters\Filter::make('invoiced')
                    ->label('Invoiced tasks')
                    ->query(fn ($query) => $query->where('status', 'invoiced')),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'todo' => 'Todo',
                        'in_progress' => 'In Progress',
                        'done' => 'Done',
                        'invoiced' => 'Invoiced',
                        'cancelled' => 'Cancelled',
                    ]),
            ])
            ->headerActions([
                Action::make('syncGithubTasks')
                    ->label('Sync GitHub Tasks')
                    ->icon('heroicon-o-arrow-path')
                    ->visible(fn (): bool => filled($this->getOwnerRecord()->github_project_url))
                    ->action(function (): void {
                        /** @var Project $project */
                        $project = $this->getOwnerRecord();

                        try {
                            $result = app(GitHubProjectSync::class)->sync($project);

                            Notification::make()
                                ->title('GitHub tasks synced')
                                ->body("Created {$result['created']}, updated {$result['updated']}, skipped {$result['skipped']}.")
                                ->success()
                                ->send();
                        } catch (\Throwable $exception) {
                            Notification::make()
                                ->title('GitHub sync failed')
                                ->body($exception->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                Action::make('createUnifiedInvoice')
                    ->label('Create Unified Invoice')
                    ->icon('heroicon-o-document-plus')
                    ->form(function (): array {
                        /** @var Project $project */
                        $project = $this->getOwnerRecord();

                        return [
                            Forms\Components\DatePicker::make('billing_month')
                                ->label('Billing month')
                                ->default(now()->startOfMonth())
                                ->live()
                                ->required(),
                            Forms\Components\CheckboxList::make('task_ids')
                                ->label('Completed non-invoiced tasks')
                                ->options(function (callable $get) use ($project): array {
                                    $month = Carbon::parse($get('billing_month') ?? now())->startOfMonth();

                                    return $project->tasks()
                                        ->where('status', 'done')
                                        ->whereNull('invoice_item_id')
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
                                ->columns(1),
                            Forms\Components\DatePicker::make('invoice_date')
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
                            ->whereNull('invoice_item_id')
                            ->whereNull('invoiced_at')
                            ->get();

                        if ($tasks->isEmpty()) {
                            Notification::make()->title('No billable tasks selected')->warning()->send();

                            return;
                        }

                        $invoice = app(InvoiceCreator::class)->forProjectTasks($project, $tasks, $data);

                        Notification::make()
                            ->title('Unified invoice created')
                            ->body($invoice->invoice_no)
                            ->success()
                            ->send();
                    }),
                CreateAction::make(),
            ])
            ->recordActions([
                Action::make('openGithubTask')
                    ->label('GitHub')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn ($record) => $record->github_task_url)
                    ->openUrlInNewTab()
                    ->visible(fn ($record) => filled($record->github_task_url)),
                Action::make('markDone')
                    ->label('Mark done')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn ($record) => in_array($record->status, ['todo', 'in_progress'], true))
                    ->action(fn ($record) => $record->update([
                        'status' => 'done',
                        'completed_at' => now(),
                    ])),
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
