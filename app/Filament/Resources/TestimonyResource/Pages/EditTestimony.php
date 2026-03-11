<?php

namespace App\Filament\Resources\TestimonyResource\Pages;

use App\Filament\Resources\TestimonyResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Width;

class EditTestimony extends EditRecord
{
    protected static string $resource = TestimonyResource::class;

    protected Width|string|null $maxContentWidth = Width::Full;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
