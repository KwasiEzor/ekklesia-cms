<?php

namespace App\Filament\Resources\GivingRecordResource\Pages;

use App\Filament\Resources\GivingRecordResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Width;

class ListGivingRecords extends ListRecords
{
    protected static string $resource = GivingRecordResource::class;

    protected Width|string|null $maxContentWidth = Width::Full;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
