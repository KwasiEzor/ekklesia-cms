<?php

namespace App\Filament\Resources\GivingRecordResource\Pages;

use App\Filament\Resources\GivingRecordResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGivingRecord extends EditRecord
{
    protected static string $resource = GivingRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
