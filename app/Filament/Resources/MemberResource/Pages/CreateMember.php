<?php

namespace App\Filament\Resources\MemberResource\Pages;

use App\Filament\Resources\MemberResource;
use App\Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Width;
use Illuminate\Support\Arr;

class CreateMember extends CreateRecord
{
    protected static string $resource = MemberResource::class;

    protected Width|string|null $maxContentWidth = Width::Full;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return Arr::except($data, ['avatar']);
    }

    protected function afterCreate(): void
    {
        $avatar = $this->data['avatar'] ?? null;

        if ($avatar) {
            $this->record->addMedia(storage_path('app/public/'.$avatar))
                ->toMediaCollection('avatar');
        }
    }
}
