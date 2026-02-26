<?php

namespace App\Filament\Resources\GalleryResource\Pages;

use App\Filament\Resources\GalleryResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Arr;

class CreateGallery extends CreateRecord
{
    protected static string $resource = GalleryResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['tenant_id'] = tenant('id');

        return Arr::except($data, ['photos']);
    }

    protected function afterCreate(): void
    {
        $photos = $this->data['photos'] ?? [];

        foreach ($photos as $photo) {
            $this->record->addMedia(storage_path('app/public/' . $photo))
                ->toMediaCollection('photos');
        }
    }
}
