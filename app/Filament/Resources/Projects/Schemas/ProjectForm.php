<?php

namespace App\Filament\Resources\Projects\Schemas;

use App\Filament\Resources\Projects\ProjectResource;
use App\Models\Client;
use Filament\Forms;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProjectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Project details')
                    ->columns(2)
                    ->components([
                        Forms\Components\Select::make('client_id')
                            ->relationship('client', 'id')
                            ->getOptionLabelFromRecordUsing(fn (Client $record): string => $record->lead?->company ?: $record->lead?->name ?: 'Client #'.$record->id)
                            ->searchable()
                            ->preload()
                            ->required()
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(function (callable $set, ?int $state): void {
                                $set('lead_id', $state ? Client::find($state)?->lead_id : null);
                                $set('quote_id', null);
                            }),
                        Forms\Components\Hidden::make('lead_id')
                            ->required(),
                        Forms\Components\Select::make('quote_id')
                            ->label('Accepted quote')
                            ->relationship('quote', 'quote_no', function ($query, $get) {
                                $query->where('status', 'accepted');

                                if ($leadId = $get('lead_id')) {
                                    $query->where('lead_id', $leadId);
                                }
                            })
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->placeholder('Optional'),
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('status')
                            ->options(ProjectResource::statuses())
                            ->default('active')
                            ->required(),
                        Forms\Components\DatePicker::make('start_date')
                            ->required()
                            ->default(now()),
                        Forms\Components\DatePicker::make('end_date')
                            ->label('End date'),
                        Forms\Components\TextInput::make('github_project_url')
                            ->label('GitHub project URL')
                            ->url()
                            ->maxLength(255)
                            ->placeholder('https://github.com/orgs/example/projects/1'),
                        Forms\Components\TextInput::make('github_repo_url')
                            ->label('GitHub repo URL')
                            ->url()
                            ->maxLength(255)
                            ->placeholder('https://github.com/example/repo'),
                        Forms\Components\Textarea::make('description')
                            ->columnSpanFull()
                            ->rows(3),
                        Forms\Components\Textarea::make('notes')
                            ->columnSpanFull()
                            ->rows(3),
                    ]),
            ]);
    }
}
