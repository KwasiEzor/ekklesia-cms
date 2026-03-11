<?php

namespace App\Filament\Resources\CampaignResource\Pages;

use App\Filament\Resources\CampaignResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Width;

class CreateCampaign extends CreateRecord
{
    protected static string $resource = CampaignResource::class;

    protected Width|string|null $maxContentWidth = Width::Full;
}
