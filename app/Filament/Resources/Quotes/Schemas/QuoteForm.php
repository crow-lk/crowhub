<?php

namespace App\Filament\Resources\Quotes\Schemas;

use App\Filament\Resources\Quotes\QuoteResource;
use App\Models\Product;
use App\Models\TermsAndCondition;
use Filament\Forms;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set as SchemaSet;
use Filament\Schemas\Schema;

class QuoteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Quote details')
                    ->columns(1)
                    ->components([
                        Forms\Components\Select::make('lead_id')
                            ->relationship('lead', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->native(false),
                        Forms\Components\TextInput::make('quote_no')
                            ->label('Quote #')
                            ->disabled()
                            ->dehydrated(),
                        Forms\Components\Select::make('status')
                            ->options(QuoteResource::statuses())
                            ->required(),
                        Forms\Components\DatePicker::make('valid_until')
                            ->label('Valid until'),
                    ]),
                Section::make('Totals')
                    ->columns(1)
                    ->components([
                        Forms\Components\TextInput::make('subtotal')
                            ->numeric()
                            ->prefix('LKR ')
                            ->default(0)
                            ->disabled()
                            ->dehydrated(false),
                        Forms\Components\TextInput::make('discount')
                            ->numeric()
                            ->prefix('LKR ')
                            ->minValue(0)
                            ->default(0),
                        Forms\Components\TextInput::make('total')
                            ->numeric()
                            ->prefix('LKR ')
                            ->default(0)
                            ->disabled()
                            ->dehydrated(false),
                    ]),
                Section::make('Items')
                    ->columnSpanFull()
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->relationship()
                            ->columnSpanFull()
                            ->columns(4)
                            ->minItems(1)
                            ->schema([
                                Forms\Components\Select::make('product_id')
                                    ->label('Product / Service')
                                    ->relationship(
                                        'product',
                                        'name',
                                        modifyQueryUsing: fn ($query) => $query
                                            ->where('is_active', true)
                                            ->orderBy('name'),
                                    )
                                    ->getOptionLabelFromRecordUsing(
                                        fn (Product $record): string => sprintf(
                                            '%s (%s)',
                                            $record->name,
                                            ucfirst($record->type)
                                        )
                                    )
                                    ->searchable()
                                    ->preload()
                                    ->native(false)
                                    ->nullable()
                                    ->columnSpan(2)
                                    ->live()
                                    ->afterStateUpdated(function (SchemaSet $set, ?int $state): void {
                                        $name = $state ? Product::find($state)?->name : null;
                                        $set('product_name', $name);
                                    }),
                                Forms\Components\TextInput::make('product_name')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpan(2),
                                Forms\Components\Textarea::make('description')
                                    ->rows(2)
                                    ->columnSpanFull(),
                                Forms\Components\TextInput::make('qty')
                                    ->numeric()
                                    ->minValue(1)
                                    ->required(),
                                Forms\Components\TextInput::make('unit_price')
                                    ->numeric()
                                    ->required()
                                    ->prefix('LKR '),
                                Forms\Components\TextInput::make('line_total')
                                    ->label('Line total')
                                    ->numeric()
                                    ->prefix('LKR ')
                                    ->disabled()
                                    ->dehydrated(false),
                            ]),
                    ]),
                Section::make('Terms & Conditions')
                    ->description('Select the terms and conditions to include on the quotation')
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
                            ->helperText('Select all terms that should appear on the quotation'),
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
