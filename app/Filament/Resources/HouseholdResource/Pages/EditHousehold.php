<?php

namespace App\Filament\Resources\HouseholdResource\Pages;

use App\Filament\Resources\HouseholdResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Width;

class EditHousehold extends EditRecord
{
    protected static string $resource = HouseholdResource::class;

    protected Width|string|null $maxContentWidth = Width::Full;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
