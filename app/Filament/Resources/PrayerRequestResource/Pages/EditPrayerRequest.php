<?php

namespace App\Filament\Resources\PrayerRequestResource\Pages;

use App\Filament\Resources\PrayerRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Width;

class EditPrayerRequest extends EditRecord
{
    protected static string $resource = PrayerRequestResource::class;

    protected Width|string|null $maxContentWidth = Width::Full;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
