<?php

namespace App\Filament\Resources\SermonResource\Pages;

use App\Filament\Resources\SermonResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSermon extends CreateRecord
{
    protected static string $resource = SermonResource::class;

    protected function afterCreate(): void
    {
        $tags = $this->data['tags'] ?? [];

        if ($tags) {
            $this->record->syncTags($tags);
        }
    }
}
