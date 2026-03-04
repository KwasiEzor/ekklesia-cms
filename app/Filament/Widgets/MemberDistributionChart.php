<?php

namespace App\Filament\Widgets;

use App\Models\Member;
use Filament\Widgets\ChartWidget;

class MemberDistributionChart extends ChartWidget
{
    protected static ?int $sort = -1;

    protected int|string|array $columnSpan = 'full';

    protected ?string $maxHeight = '300px';

    public function getHeading(): string
    {
        return __('dashboard.member_distribution');
    }

    public function getDescription(): string
    {
        return __('dashboard.member_distribution_desc');
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getData(): array
    {
        $statuses = ['active', 'inactive', 'visiting', 'transferred'];
        $labels = [];
        $data = [];

        foreach ($statuses as $status) {
            $labels[] = __("members.statuses.{$status}");
            $data[] = Member::where('status', $status)->count();
        }

        return [
            'datasets' => [
                [
                    'data' => $data,
                    'backgroundColor' => [
                        '#10b981', // success - active
                        '#ef4444', // danger - inactive
                        '#6366f1', // primary - visiting
                        '#f59e0b', // warning - transferred
                    ],
                    'borderWidth' => 0,
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
                    'position' => 'bottom',
                ],
            ],
            'cutout' => '70%',
        ];
    }
}
