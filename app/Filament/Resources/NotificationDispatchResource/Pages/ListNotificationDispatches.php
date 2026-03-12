<?php

namespace App\Filament\Resources\NotificationDispatchResource\Pages;

use App\Filament\Resources\NotificationDispatchResource;
use App\Filament\Resources\NotificationDispatchResource\Widgets\NotificationStats;
use App\Filament\Resources\NotificationDispatchResource\Widgets\NotificationTrends;
use App\Filament\Resources\NotificationDispatchResource\Widgets\RecentNotificationFailures;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Width;

class ListNotificationDispatches extends ListRecords
{
    protected static string $resource = NotificationDispatchResource::class;

    protected Width|string|null $maxContentWidth = Width::Full;

    protected function getHeaderWidgets(): array
    {
        return [
            NotificationStats::class,
            NotificationTrends::class,
            RecentNotificationFailures::class,
        ];
    }
}
