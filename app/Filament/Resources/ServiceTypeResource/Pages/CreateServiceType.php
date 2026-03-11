<?php

namespace App\Filament\Resources\ServiceTypeResource\Pages;

use App\Filament\Resources\ServiceTypeResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Width;

class CreateServiceType extends CreateRecord
{
    protected static string $resource = ServiceTypeResource::class;

    protected Width|string|null $maxContentWidth = Width::Full;
}
