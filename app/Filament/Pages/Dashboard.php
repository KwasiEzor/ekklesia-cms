<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    /**
     * @return int | array<string, ?int>
     */
    public function getColumns(): int|array
    {
        return [
            'default' => 1,
            'sm' => 2,
            'lg' => 3,
        ];
    }

    public function getTitle(): string
    {
        return __('dashboard.control_center');
    }

    public function getSubheading(): ?string
    {
        return __('dashboard.control_center_subheading');
    }
}
