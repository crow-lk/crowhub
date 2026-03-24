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
                            ->native(false),
                        Forms\Components\Select::make('quote_id')
                            ->label('Related quote')
                            ->relationship('quote', 'quote_no')
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
                    ->description('Select the terms and conditions to include on the invoice')
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
                            ->helperText('Select all terms that should appear on the invoice'),
                    ]),
            ]);
    }

    /**
     * Get terms and conditions as nested options for checkbox list
     */
    private static function getTermsOptions(): array
    {
        $terms = TermsAndCondition::active()
            ->with('children')
            ->roots()
            ->orderBy('sort_order')
            ->orderBy('number')
            ->get();

        $options = [];

        foreach ($terms as $term) {
            $termTitle = $term->title ?? strip_tags($term->content);
            $options[$term->id] = $term->number
                ? "{$term->number}. {$termTitle}"
                : $termTitle;

            // Add children as indented options
            foreach ($term->children as $child) {
                $childTitle = $child->title ?? strip_tags($child->content);
                $options[$child->id] = '  ' . ($child->number
                    ? "{$child->number}. {$childTitle}"
                    : $childTitle);

                // Add nested children
                $options = self::addChildOptions($child, $options, 4);
            }
        }

        return $options;
    }

    /**
     * Recursively add child terms to options array
     */
    private static function addChildOptions($parent, array &$options, int $indent = 4): array
    {
        foreach ($parent->children as $child) {
            $spacing = str_repeat(' ', $indent);
            $childTitle = $child->title ?? strip_tags($child->content);
            $options[$child->id] = $spacing . ($child->number
                ? "{$child->number}. {$childTitle}"
                : $childTitle);

            $options = self::addChildOptions($child, $options, $indent + 2);
        }

        return $options;
    }
}
