<?php

namespace App\Filament\Resources\TermsAndCondition\Schemas;

use App\Models\TermsAndCondition;
use Filament\Forms;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TermsAndConditionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Term Details')
                    ->columns(2)
                    ->components([
                        Forms\Components\TextInput::make('number')
                            ->label('Number')
                            ->placeholder('e.g., 1, 1.1, 1.2, 2, 2.1')
                            ->helperText('Hierarchical number (e.g., 1 for main, 1.1 for sub-item)')
                            ->maxLength(20),
                        Forms\Components\TextInput::make('title')
                            ->label('Title')
                            ->maxLength(255)
                            ->placeholder('Optional short title'),
                        Forms\Components\Select::make('parent_id')
                            ->label('Parent Term')
                            ->placeholder('None (Root level)')
                            ->options(function () {
                                return TermsAndCondition::roots()
                                    ->get()
                                    ->mapWithKeys(fn ($term) => [$term->id => $term->display_name])
                                    ->toArray();
                            })
                            ->searchable()
                            ->preload()
                            ->native(false),
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
