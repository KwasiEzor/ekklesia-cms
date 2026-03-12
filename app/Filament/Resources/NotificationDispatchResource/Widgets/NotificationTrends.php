<?php

namespace App\Filament\Resources\NotificationDispatchResource\Widgets;

use App\Models\NotificationDispatch;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class NotificationTrends extends ChartWidget
{
    protected ?string $heading = 'Notification Volume (Last 7 Days)';

    protected ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $days = collect(range(6, 0))->map(fn ($i) => now()->subDays($i)->format('Y-m-d'));

        $data = NotificationDispatch::query()
            ->select(DB::raw('DATE(created_at) as date'), 'status', DB::raw('count(*) as count'))
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->groupBy('date', 'status')
            ->get();

        $sentData = $days->map(fn ($day) => $data->where('date', $day)->where('status', 'sent')->sum('count'));
        $failedData = $days->map(fn ($day) => $data->where('date', $day)->where('status', 'failed')->sum('count'));

        return [
            'datasets' => [
                [
                    'label' => 'Sent',
                    'data' => $sentData->toArray(),
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'fill' => 'start',
                ],
                [
                    'label' => 'Failed',
                    'data' => $failedData->toArray(),
                    'borderColor' => '#ef4444',
                    'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
                    'fill' => 'start',
                ],
            ],
            'labels' => $days->map(fn ($day) => Carbon::parse($day)->format('D'))->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
