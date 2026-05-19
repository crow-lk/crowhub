<?php

namespace App\Filament\Resources\Projects\Schemas;

use Filament\Infolists;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProjectInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Project')
                    ->columns(2)
                    ->components([
                        Infolists\Components\TextEntry::make('name'),
                        Infolists\Components\TextEntry::make('status')
                            ->badge(),
                        Infolists\Components\TextEntry::make('client.lead.name')
                            ->label('Client'),
                        Infolists\Components\TextEntry::make('lead.company')
                            ->label('Company')
                            ->placeholder('-'),
                        Infolists\Components\TextEntry::make('quote.quote_no')
                            ->label('Accepted quote')
                            ->placeholder('-'),
                        Infolists\Components\TextEntry::make('start_date')
                            ->date(),
                        Infolists\Components\TextEntry::make('end_date')
                            ->date()
                            ->placeholder('-'),
                    ]),
                Section::make('GitHub')
                    ->columns(2)
                    ->components([
                        Infolists\Components\TextEntry::make('github_project_url')
                            ->label('Project board')
                            ->url(fn ($record) => $record->github_project_url)
                            ->openUrlInNewTab()
                            ->placeholder('-'),
                        Infolists\Components\TextEntry::make('github_repo_url')
                            ->label('Repository')
                            ->url(fn ($record) => $record->github_repo_url)
                            ->openUrlInNewTab()
                            ->placeholder('-'),
                    ]),
                Section::make('Description')
                    ->hidden(fn ($record) => blank($record->description))
                    ->components([
                        Infolists\Components\TextEntry::make('description')
                            ->columnSpanFull()
                            ->markdown(),
                    ]),
                Section::make('Notes')
                    ->hidden(fn ($record) => blank($record->notes))
                    ->components([
                        Infolists\Components\TextEntry::make('notes')
                            ->columnSpanFull()
                            ->markdown(),
                    ]),
            ]);
    }
}
