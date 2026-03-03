<?php

namespace App\Filament\Widgets;

use App\Models\Event;
use App\Models\GivingRecord;
use App\Models\Member;
use App\Models\Sermon;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = -3;

    protected int | string | array $columnSpan = 'full';

    protected function getColumns(): int | array | null
    {
        return 4;
    }

    protected function getStats(): array
    {
        $activeMembers = Member::where('status', 'active')->count();
        $givingThisMonth = GivingRecord::whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->sum('amount');
        $givingLastMonth = GivingRecord::whereMonth('date', now()->subMonth()->month)
            ->whereYear('date', now()->subMonth()->year)
            ->sum('amount');
        $givingChange = $givingLastMonth > 0
            ? round(($givingThisMonth - $givingLastMonth) / $givingLastMonth * 100, 1)
            : 0;

        $upcomingEvents = Event::where('start_at', '>', now())->count();
        $totalSermons = Sermon::count();

        return [
            Stat::make(
                __('dashboard.active_members'),
                number_format($activeMembers),
            )
                ->description(__('dashboard.active_members_desc'))
                ->descriptionIcon(Heroicon::OutlinedArrowTrendingUp)
                ->color('success')
                ->chart($this->getMonthlyCountChart(Member::class, 'created_at')),

            Stat::make(
                __('dashboard.giving_this_month'),
                number_format((float) $givingThisMonth, 0, ',', ' '),
            )
                ->description(($givingChange >= 0 ? '+' : '') . $givingChange . '% ' . __('dashboard.vs_last_month'))
                ->descriptionIcon($givingChange >= 0 ? Heroicon::OutlinedArrowTrendingUp : Heroicon::OutlinedArrowTrendingDown)
                ->color($givingChange >= 0 ? 'success' : 'danger')
                ->chart($this->getMonthlyGivingChart()),

            Stat::make(
                __('dashboard.upcoming_events'),
                number_format($upcomingEvents),
            )
                ->description(__('dashboard.upcoming_events_desc'))
                ->descriptionIcon(Heroicon::OutlinedCalendarDays)
                ->color('info')
                ->chart($this->getMonthlyCountChart(Event::class, 'start_at')),

            Stat::make(
                __('dashboard.sermons'),
                number_format($totalSermons),
            )
                ->description(__('dashboard.sermons_desc'))
                ->descriptionIcon(Heroicon::OutlinedMicrophone)
                ->color('primary')
                ->chart($this->getMonthlyCountChart(Sermon::class, 'date')),
        ];
    }

    /**
     * @return array<float>
     */
    protected function getMonthlyCountChart(string $model, string $dateColumn): array
    {
        $data = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $data[] = (float) $model::whereMonth($dateColumn, $date->month)
                ->whereYear($dateColumn, $date->year)
                ->count();
        }

        return $data;
    }

    /**
     * @return array<float>
     */
    protected function getMonthlyGivingChart(): array
    {
        $data = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $data[] = (float) GivingRecord::whereMonth('date', $date->month)
                ->whereYear('date', $date->year)
                ->sum('amount');
        }

        return $data;
    }
}
