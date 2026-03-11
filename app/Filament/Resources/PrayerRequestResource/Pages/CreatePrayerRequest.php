<?php

namespace App\Filament\Resources\PrayerRequestResource\Pages;

use App\Filament\Resources\PrayerRequestResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Width;

class CreatePrayerRequest extends CreateRecord
{
    protected static string $resource = PrayerRequestResource::class;

    protected Width|string|null $maxContentWidth = Width::Full;
}
