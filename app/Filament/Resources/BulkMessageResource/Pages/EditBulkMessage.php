<?php

namespace App\Filament\Resources\BulkMessageResource\Pages;

use App\Filament\Resources\BulkMessageResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Width;

class EditBulkMessage extends EditRecord
{
    protected static string $resource = BulkMessageResource::class;

    protected Width|string|null $maxContentWidth = Width::Full;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
