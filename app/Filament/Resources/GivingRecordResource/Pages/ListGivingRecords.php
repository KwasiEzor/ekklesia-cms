<?php

namespace App\Filament\Resources\GivingRecordResource\Pages;

use App\Filament\Resources\GivingRecordResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGivingRecords extends ListRecords
{
    protected static string $resource = GivingRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
