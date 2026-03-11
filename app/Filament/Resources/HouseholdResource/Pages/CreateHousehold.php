<?php

namespace App\Filament\Resources\HouseholdResource\Pages;

use App\Filament\Resources\HouseholdResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Width;

class CreateHousehold extends CreateRecord
{
    protected static string $resource = HouseholdResource::class;

    protected Width|string|null $maxContentWidth = Width::Full;
}
