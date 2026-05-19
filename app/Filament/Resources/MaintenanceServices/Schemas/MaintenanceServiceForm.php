<?php

namespace App\Filament\Resources\MaintenanceServices\Schemas;

use Filament\Forms;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get as SchemaGet;
use Filament\Schemas\Components\Utilities\Set as SchemaSet;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class MaintenanceServiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Service')
                    ->columns(1)
                    ->components([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (SchemaSet $set, ?string $state, SchemaGet $get): void {
                                if ($get('slug')) {
                                    return;
                                }

                                $set('slug', Str::slug($state ?? ''));
                            }),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('category')
                            ->maxLength(255)
                            ->placeholder('Marketing, Web, Support'),
                        Forms\Components\TextInput::make('default_monthly_fee')
                            ->label('Default monthly fee')
                            ->numeric()
                            ->prefix('LKR ')
                            ->minValue(0),
                        Forms\Components\Toggle::make('is_active')
                            ->default(true),
                        Forms\Components\Textarea::make('description')
                            ->rows(3),
                    ]),
            ]);
    }
}
