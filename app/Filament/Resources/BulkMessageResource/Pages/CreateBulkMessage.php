<?php

namespace App\Filament\Resources\BulkMessageResource\Pages;

use App\Filament\Resources\BulkMessageResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Width;

class CreateBulkMessage extends CreateRecord
{
    protected static string $resource = BulkMessageResource::class;

    protected Width|string|null $maxContentWidth = Width::Full;
}
