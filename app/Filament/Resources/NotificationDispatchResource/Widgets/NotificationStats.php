<?php

namespace App\Filament\Resources\NotificationDispatchResource\Widgets;

use App\Models\NotificationDispatch;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class NotificationStats extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $stats = NotificationDispatch::query()
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $total = array_sum($stats);
        $sent = $stats['sent'] ?? 0;
        $failed = $stats['failed'] ?? 0;
        $pending = $stats['pending'] ?? 0;

        return [
            Stat::make('Total Notifications', $total)
                ->description('All time dispatches')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('info'),
            Stat::make('Sent Successfully', $sent)
                ->description('Successfully delivered')
                ->icon('heroicon-m-check-circle')
                ->color('success'),
            Stat::make('Failed Dispatches', $failed)
                ->description('Failed to send')
                ->icon('heroicon-m-x-circle')
                ->color('danger'),
        ];
    }
}
