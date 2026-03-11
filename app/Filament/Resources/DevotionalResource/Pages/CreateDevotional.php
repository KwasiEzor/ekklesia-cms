<?php

namespace App\Filament\Resources\DevotionalResource\Pages;

use App\Filament\Resources\DevotionalResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Width;

class CreateDevotional extends CreateRecord
{
    protected static string $resource = DevotionalResource::class;

    protected Width|string|null $maxContentWidth = Width::Full;
}
