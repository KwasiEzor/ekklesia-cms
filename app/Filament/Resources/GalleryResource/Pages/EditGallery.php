<?php

namespace App\Filament\Resources\GalleryResource\Pages;

use App\Filament\Resources\GalleryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Arr;

class EditGallery extends EditRecord
{
    protected static string $resource = GalleryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['photos'] = $this->record->getMedia('photos')
            ->map(fn ($media) => $media->getUrl())
            ->toArray();

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return Arr::except($data, ['photos']);
    }

    protected function afterSave(): void
    {
        $photos = $this->data['photos'] ?? [];

        // Sync: clear existing and re-add new uploads (non-URL entries are new files)
        $newPhotos = array_filter($photos, fn ($p) => ! str_starts_with($p, 'http'));

        if ($newPhotos) {
            foreach ($newPhotos as $photo) {
                $this->record->addMedia(storage_path('app/public/' . $photo))
                    ->toMediaCollection('photos');
            }
        }
    }
}
