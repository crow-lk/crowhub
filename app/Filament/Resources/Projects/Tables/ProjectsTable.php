<?php

namespace App\Filament\Resources\Projects\Tables;

use App\Filament\Resources\Projects\ProjectResource;
use App\Models\Project;
use App\Services\GitHub\GitHubProjectSync;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProjectsTable
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
                TextColumn::make('uninvoiced_total')
                    ->label('Uninvoiced')
                    ->money('lkr')
                    ->state(fn (Project $record): float => (float) $record->tasks()
                        ->where('status', 'done')
                        ->whereNull('invoiced_at')
                        ->sum('amount')),
                TextColumn::make('open_tasks')
                    ->label('Open tasks')
                    ->state(fn (Project $record): int => $record->tasks()
                        ->whereIn('status', ['todo', 'in_progress', 'done'])
                        ->count())
                    ->badge(),
                TextColumn::make('latest_invoice')
                    ->label('Latest invoice')
                    ->state(function (Project $record): string {
                        $invoice = $record->invoices()->latest('due_date')->first();

                        if (! $invoice) {
                            return 'No invoices';
                        }

                        return $invoice->due_date?->format('M d, Y').' · '.ucfirst($invoice->status);
                    })
                    ->badge()
                    ->color(function (Project $record): string {
                        $invoice = $record->invoices()->latest('due_date')->first();

                        return match ($invoice?->status) {
                            'paid' => 'success',
                            'sent' => 'warning',
                            'overdue' => 'danger',
                            default => 'gray',
                        };
                    }),
                TextColumn::make('updated_at')
                    ->since()
                    ->label('Updated'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(ProjectResource::statuses()),
            ])
            ->recordActions([
                Action::make('openGithubProject')
                    ->label('GitHub Project')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (Project $record) => $record->github_project_url)
                    ->openUrlInNewTab()
                    ->visible(fn (Project $record) => filled($record->github_project_url)),
                Action::make('openGithubRepo')
                    ->label('GitHub Repo')
                    ->icon('heroicon-o-code-bracket')
                    ->url(fn (Project $record) => $record->github_repo_url)
                    ->openUrlInNewTab()
                    ->visible(fn (Project $record) => filled($record->github_repo_url)),
                Action::make('syncGithubTasks')
                    ->label('Sync Tasks')
                    ->icon('heroicon-o-arrow-path')
                    ->visible(fn (Project $record) => filled($record->github_project_url))
                    ->action(function (Project $record): void {
                        try {
                            $result = app(GitHubProjectSync::class)->sync($record);

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
