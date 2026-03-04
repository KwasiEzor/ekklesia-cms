<?php

namespace App\Filament\Widgets;

use App\Models\Event;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class UpcomingEventsWidget extends TableWidget
{
    protected static ?int $sort = 0;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Event::query()
                    ->where('start_at', '>', now())
                    ->orderBy('start_at')
                    ->limit(5)
            )
            ->heading(__('dashboard.upcoming_events_table'))
            ->description(__('dashboard.upcoming_events_table_desc'))
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label(__('dashboard.event_title'))
                    ->searchable(),

                Tables\Columns\TextColumn::make('start_at')
                    ->label(__('dashboard.event_date'))
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('location')
                    ->label(__('dashboard.event_location')),

                Tables\Columns\TextColumn::make('capacity')
                    ->label(__('dashboard.event_capacity'))
                    ->numeric(),
            ])
            ->emptyStateHeading(__('dashboard.no_upcoming_events'))
            ->paginated(false);
    }
}
