<?php

namespace App\Filament\Resources\GivingRecordResource\Pages;

use App\Filament\Resources\GivingRecordResource;
use App\Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Width;

class CreateGivingRecord extends CreateRecord
{
    protected static string $resource = GivingRecordResource::class;

    protected Width|string|null $maxContentWidth = Width::Full;
}
