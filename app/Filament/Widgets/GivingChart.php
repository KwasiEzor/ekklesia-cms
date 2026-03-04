<?php

namespace App\Filament\Widgets;

use App\Models\GivingRecord;
use Filament\Widgets\ChartWidget;

class GivingChart extends ChartWidget
{
    protected static ?int $sort = -2;

    protected int|string|array $columnSpan = 'full';

    protected ?string $maxHeight = '300px';

    public function getHeading(): string
    {
        return __('dashboard.giving_chart');
    }

    public function getDescription(): string
    {
        return __('dashboard.giving_chart_desc');
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $labels = [];
        $data = [];

        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $labels[] = $date->translatedFormat('M');
            $data[] = (float) GivingRecord::whereMonth('date', $date->month)
                ->whereYear('date', $date->year)
                ->sum('amount');
        }

        return [
            'datasets' => [
                [
                    'label' => __('dashboard.giving_chart_label'),
                    'data' => $data,
                    'backgroundColor' => '#6366f1',
                    'borderRadius' => 4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'grid' => [
                        'display' => true,
                    ],
                ],
                'x' => [
                    'grid' => [
                        'display' => false,
                    ],
                ],
            ],
        ];
    }
}
