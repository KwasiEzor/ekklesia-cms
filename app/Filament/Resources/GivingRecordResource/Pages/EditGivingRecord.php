<?php

namespace App\Filament\Resources\GivingRecordResource\Pages;

use App\Filament\Resources\GivingRecordResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Width;

class EditGivingRecord extends EditRecord
{
    protected static string $resource = GivingRecordResource::class;

    protected Width | string | null $maxContentWidth = Width::Full;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
