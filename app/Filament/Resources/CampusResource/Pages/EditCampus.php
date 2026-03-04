<?php

namespace App\Filament\Resources\CampusResource\Pages;

use App\Filament\Resources\CampusResource;
use App\Filament\Resources\Pages\EditRecord;
use Filament\Actions;
use Filament\Support\Enums\Width;

class EditCampus extends EditRecord
{
    protected static string $resource = CampusResource::class;

    protected Width|string|null $maxContentWidth = Width::Full;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
