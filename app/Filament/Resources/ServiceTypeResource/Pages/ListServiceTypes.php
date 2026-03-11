<?php

namespace App\Filament\Resources\ServiceTypeResource\Pages;

use App\Filament\Resources\ServiceTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Width;

class ListServiceTypes extends ListRecords
{
    protected static string $resource = ServiceTypeResource::class;

    protected Width|string|null $maxContentWidth = Width::Full;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
