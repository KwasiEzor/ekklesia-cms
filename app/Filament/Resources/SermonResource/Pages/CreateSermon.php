<?php

namespace App\Filament\Resources\SermonResource\Pages;

use App\Filament\Resources\SermonResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Width;

class CreateSermon extends CreateRecord
{
    protected static string $resource = SermonResource::class;

    protected Width | string | null $maxContentWidth = Width::Full;

    protected function afterCreate(): void
    {
        $tags = $this->data['tags'] ?? [];

        if ($tags) {
            $this->record->syncTags($tags);
        }
    }
}
