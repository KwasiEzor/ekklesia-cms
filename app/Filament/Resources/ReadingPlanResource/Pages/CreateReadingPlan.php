<?php

namespace App\Filament\Resources\ReadingPlanResource\Pages;

use App\Filament\Resources\ReadingPlanResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Width;

class CreateReadingPlan extends CreateRecord
{
    protected static string $resource = ReadingPlanResource::class;

    protected Width|string|null $maxContentWidth = Width::Full;
}
