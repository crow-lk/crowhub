<?php

namespace App\Filament\Resources\TermsAndCondition\Pages;

use App\Filament\Resources\TermsAndCondition\TermsAndConditionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTermsAndConditions extends ListRecords
{
    protected static string $resource = TermsAndConditionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
