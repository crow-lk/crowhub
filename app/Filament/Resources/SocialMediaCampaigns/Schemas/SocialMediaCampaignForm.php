<?php

namespace App\Filament\Resources\SocialMediaCampaigns\Schemas;

use App\Filament\Resources\SocialMediaCampaigns\SocialMediaCampaignResource;
use App\Models\Client;
use Filament\Forms;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SocialMediaCampaignForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Campaign')
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
                                $set('maintenance_contract_id', null);
                            }),
                        Forms\Components\Hidden::make('lead_id')
                            ->required(),
                        Forms\Components\Select::make('maintenance_contract_id')
                            ->label('Maintenance contract')
                            ->relationship('maintenanceContract', 'id', function ($query, $get) {
                                if ($leadId = $get('lead_id')) {
                                    $query->where('lead_id', $leadId);
                                }
                            })
                            ->getOptionLabelFromRecordUsing(fn ($record): string => 'Contract #'.$record->id.' - LKR '.number_format((float) $record->monthly_fee, 2))
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->placeholder('Optional'),
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('status')
                            ->options(SocialMediaCampaignResource::statuses())
                            ->default('planned')
                            ->required(),
                        Forms\Components\CheckboxList::make('platforms')
                            ->options(SocialMediaCampaignResource::platforms())
                            ->columns(2),
                        Forms\Components\TextInput::make('budget')
                            ->numeric()
                            ->prefix('LKR ')
                            ->minValue(0),
                        Forms\Components\DatePicker::make('start_date'),
                        Forms\Components\DatePicker::make('end_date'),
                        Forms\Components\TextInput::make('objective')
                            ->columnSpanFull()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('content_plan')
                            ->columnSpanFull()
                            ->rows(4),
                        Forms\Components\Textarea::make('results_summary')
                            ->columnSpanFull()
                            ->rows(4),
                        Forms\Components\Textarea::make('notes')
                            ->columnSpanFull()
                            ->rows(3),
                    ]),
            ]);
    }
}
