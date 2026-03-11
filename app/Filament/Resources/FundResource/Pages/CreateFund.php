<?php

namespace App\Filament\Resources\FundResource\Pages;

use App\Filament\Resources\FundResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Width;

class CreateFund extends CreateRecord
{
    protected static string $resource = FundResource::class;

    protected Width|string|null $maxContentWidth = Width::Full;
}
