<?php

namespace App\Filament\Resources\TestimonyResource\Pages;

use App\Filament\Resources\TestimonyResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Width;

class CreateTestimony extends CreateRecord
{
    protected static string $resource = TestimonyResource::class;

    protected Width|string|null $maxContentWidth = Width::Full;
}
