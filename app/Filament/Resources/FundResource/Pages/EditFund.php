<?php

namespace App\Filament\Resources\FundResource\Pages;

use App\Filament\Resources\FundResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Width;

class EditFund extends EditRecord
{
    protected static string $resource = FundResource::class;

    protected Width|string|null $maxContentWidth = Width::Full;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
