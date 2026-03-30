<?php

namespace App\Filament\Resources\TermsAndCondition\Schemas;

use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TermsAndConditionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Main Term')
                    ->columns(1)
                    ->components([
                        Forms\Components\TextInput::make('sort_order')
                            ->label('Sort Order')
                            ->numeric()
                            ->default(0)
                            ->helperText('Lower numbers appear first'),
                    ]),
                Section::make('Content')
                    ->columns(1)
                    ->components([
                        Forms\Components\RichEditor::make('content')
                            ->label('Terms Content')
                            ->required()
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'strike',
                                'bulletList',
                                'orderedList',
                                'undo',
                                'redo',
                            ])
                            ->columnSpanFull(),
                    ]),
                Section::make('Secondary Terms')
                    ->description('Add secondary terms that will be linked to this main term')
                    ->schema([
                        Repeater::make('secondary_terms')
                            ->label('')
                            ->schema([
                                Forms\Components\RichEditor::make('content')
                                    ->label('Content')
                                    ->required()
                                    ->toolbarButtons([
                                        'bold',
                                        'italic',
                                        'underline',
                                        'bulletList',
                                        'orderedList',
                                        'undo',
                                        'redo',
                                    ])
                                    ->columnSpanFull(),
                            ])
                            ->columns(1)
                            ->collapsible()
                            ->itemLabel(fn (array $state, $livewire): string => 'Term ' . (array_search($state, $livewire->data['secondary_terms'] ?? [], true) + 1))
                    ]),
                Section::make('Status')
                    ->columns(1)
                    ->components([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->helperText('Inactive terms will not appear in selection lists'),
                    ]),
            ]);
    }
}
