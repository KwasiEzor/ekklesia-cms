<?php

namespace App\Filament\Resources\CampusResource\Pages;

use App\Filament\Resources\CampusResource;
use App\Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Width;

class CreateCampus extends CreateRecord
{
    protected static string $resource = CampusResource::class;

    protected Width|string|null $maxContentWidth = Width::Full;
}
