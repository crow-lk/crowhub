<?php

namespace App\Filament\Resources\Payments\Schemas;

use App\Filament\Resources\Payments\PaymentResource;
use App\Models\TermsAndCondition;
use Filament\Forms;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Payment details')
                    ->columns(1)
                    ->components([
                        Forms\Components\Select::make('lead_id')
                            ->relationship('lead', 'name')
                            ->searchable()
                            ->required()
                            ->preload()
                            ->native(false)
                            ->afterStateUpdated(function (callable $set) {
                                $set('quote_id', null);
                                $set('invoice_id', null);
                                $set('social_media_campaign_id', null);
                            }),
                        Forms\Components\Select::make('quote_id')
                            ->label('Related quote')
                            ->relationship('quote', 'quote_no', function ($query, $get) {
                                if ($leadId = $get('lead_id')) {
                                    $query->where('lead_id', $leadId);
                                }
                            })
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->placeholder('Optional')
                            ->live()
                            ->afterStateUpdated(function (callable $set, $state) {
                                if ($state) {
                                    $quote = \App\Models\Quote::find($state);
                                    if ($quote) {
                                        $set('amount', $quote->total);
                                        $set('amount_to_pay', $quote->total - $quote->paidAmount());
                                        $set('to_pay', $quote->total - $quote->paidAmount());
                                    }
                                } else {
                                    $set('amount', null);
                                    $set('amount_to_pay', null);
                                    $set('to_pay', null);
                                }
                            }),
                        Forms\Components\Select::make('invoice_id')
                            ->label('Related invoice')
                            ->relationship('invoice', 'invoice_no', function ($query, $get) {
                                if ($leadId = $get('lead_id')) {
                                    $query->where('lead_id', $leadId);
                                }
                            })
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->placeholder('Optional')
                            ->afterStateUpdated(function (callable $set, $state) {
                                if ($state) {
                                    $invoice = \App\Models\Invoice::find($state);
                                    if ($invoice) {
                                        $set('amount', $invoice->total);
                                        $set('amount_to_pay', $invoice->total - $invoice->paidAmount());
                                        $set('to_pay', $invoice->total - $invoice->paidAmount());
                                    }
                                } else {
                                    $set('amount', null);
                                    $set('amount_to_pay', null);
                                    $set('to_pay', null);
                                }
                            }),
                        Forms\Components\Select::make('social_media_campaign_id')
                            ->label('Related social media campaign')
                            ->relationship('socialMediaCampaign', 'name', function ($query, $get) {
                                if ($leadId = $get('lead_id')) {
                                    $query->where('lead_id', $leadId);
                                }
                            })
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->placeholder('Optional'),
                        Forms\Components\TextInput::make('amount')
                            ->numeric()
                            ->disabled()
                            ->prefix('LKR ')
                            ->label('From Quote/Invoice')
                            ->live(),
                        Forms\Components\TextInput::make('amount_to_pay')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->prefix('LKR ')
                            ->live(debounce: 500),
                        Forms\Components\TextInput::make('to_pay')
                            ->numeric()
                            ->prefix('LKR ')
                            ->disabled()
                            ->helperText('Balance to pay')
                            ->live(),
                        Forms\Components\Select::make('type')
                            ->options(PaymentResource::types())
                            ->required()
                            ->default('other'),
                        Forms\Components\DatePicker::make('due_date')
                            ->required()
                            ->default(now()),
                        Forms\Components\TextInput::make('method')
                            ->maxLength(255),
                        Forms\Components\Textarea::make('note')
                            ->columnSpanFull()
                            ->rows(3),
                    ]),
                Section::make('Terms & Conditions')
                    ->description('Select the main terms and conditions. Secondary terms will be automatically included.')
                    ->columns(1)
                    ->components([
                        Forms\Components\CheckboxList::make('termsAndConditions')
                            ->label('')
                            ->relationship('termsAndConditions', 'id')
                            ->options(function () {
                                return self::getTermsOptions();
                            })
                            ->columns(1)
                            ->gridDirection('column')
                            ->helperText('Select main terms. Secondary terms will be automatically included when displaying.'),
                    ]),
            ]);
    }

    /**
     * Get terms and conditions as options for checkbox list
     */
    private static function getTermsOptions(): array
    {
        $terms = TermsAndCondition::active()
            ->orderBy('sort_order')
            ->orderBy('content')
            ->get();

        $options = [];

        foreach ($terms as $term) {
            $termContent = strip_tags($term->content);
            // Truncate content if too long
            if (strlen($termContent) > 80) {
                $termContent = substr($termContent, 0, 80).'...';
            }

            $options[$term->id] = $termContent;
        }

        return $options;
    }
}
