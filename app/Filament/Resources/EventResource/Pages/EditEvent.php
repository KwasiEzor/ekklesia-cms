<?php

namespace App\Filament\Resources\EventResource\Pages;

use App\Filament\Resources\EventResource;
use App\Filament\Resources\Pages\EditRecord;
use Filament\Actions;
use Filament\Support\Enums\Width;

class EditEvent extends EditRecord
{
    protected static string $resource = EventResource::class;

    protected Width|string|null $maxContentWidth = Width::Full;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
