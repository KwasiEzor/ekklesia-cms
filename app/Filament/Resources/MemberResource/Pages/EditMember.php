<?php

namespace App\Filament\Resources\MemberResource\Pages;

use App\Filament\Resources\MemberResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Arr;

class EditMember extends EditRecord
{
    protected static string $resource = MemberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $avatar = $this->record->getFirstMediaUrl('avatar');
        $data['avatar'] = $avatar ?: null;

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return Arr::except($data, ['avatar']);
    }

    protected function afterSave(): void
    {
        $avatar = $this->data['avatar'] ?? null;

        if ($avatar && ! str_starts_with($avatar, 'http')) {
            $this->record->clearMediaCollection('avatar');
            $this->record->addMedia(storage_path('app/public/' . $avatar))
                ->toMediaCollection('avatar');
        } elseif (empty($avatar)) {
            $this->record->clearMediaCollection('avatar');
        }
    }
}
