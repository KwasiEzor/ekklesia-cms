<?php

namespace App\Filament\Resources\DevotionalResource\Pages;

use App\Filament\Resources\DevotionalResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Width;

class EditDevotional extends EditRecord
{
    protected static string $resource = DevotionalResource::class;

    protected Width|string|null $maxContentWidth = Width::Full;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
