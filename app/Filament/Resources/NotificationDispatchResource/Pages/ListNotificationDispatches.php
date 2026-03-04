<?php

namespace App\Filament\Resources\NotificationDispatchResource\Pages;

use App\Filament\Resources\NotificationDispatchResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Width;

class ListNotificationDispatches extends ListRecords
{
    protected static string $resource = NotificationDispatchResource::class;

    protected Width|string|null $maxContentWidth = Width::Full;
}
