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
                            ->placeholder('Optional'),
                        Forms\Components\TextInput::make('amount')
                            ->numeric()
                            ->required()
                            ->minValue(0.01)
                            ->prefix('LKR '),
                        Forms\Components\Select::make('type')
                            ->options(PaymentResource::types())
                            ->required()
                            ->default('other'),
                        Forms\Components\DatePicker::make('paid_date')
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
                $termContent = substr($termContent, 0, 80) . '...';
            }

            $options[$term->id] = $termContent;
        }

        return $options;
    }
}
