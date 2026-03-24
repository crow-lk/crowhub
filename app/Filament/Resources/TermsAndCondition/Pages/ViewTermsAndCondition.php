<?php

namespace App\Filament\Resources\TermsAndCondition\Pages;

use App\Filament\Resources\TermsAndCondition\TermsAndConditionResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewTermsAndCondition extends ViewRecord
{
    protected static string $resource = TermsAndConditionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
