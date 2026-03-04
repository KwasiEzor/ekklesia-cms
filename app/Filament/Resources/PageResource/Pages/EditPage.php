<?php

namespace App\Filament\Resources\PageResource\Pages;

use App\Filament\Resources\PageResource;
use App\Filament\Resources\Pages\EditRecord;
use Filament\Actions;
use Filament\Support\Enums\Width;

class EditPage extends EditRecord
{
    protected static string $resource = PageResource::class;

    protected Width|string|null $maxContentWidth = Width::Full;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
