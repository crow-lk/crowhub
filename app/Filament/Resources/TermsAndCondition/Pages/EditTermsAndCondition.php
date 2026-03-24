<?php

namespace App\Filament\Resources\TermsAndCondition\Pages;

use App\Filament\Resources\TermsAndCondition\TermsAndConditionResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditTermsAndCondition extends EditRecord
{
    protected static string $resource = TermsAndConditionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
