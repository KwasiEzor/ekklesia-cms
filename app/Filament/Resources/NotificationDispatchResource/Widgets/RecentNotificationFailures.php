<?php

namespace App\Filament\Resources\NotificationDispatchResource\Widgets;

use App\Models\NotificationDispatch;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class RecentNotificationFailures extends TableWidget
{
    protected static ?string $heading = 'Recent Failures';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                fn (): Builder => NotificationDispatch::query()
                    ->where('status', 'failed')
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('channel')
                    ->badge()
                    ->color('danger'),
                Tables\Columns\TextColumn::make('type')
                    ->formatStateUsing(fn ($state) => ucfirst(str_replace('_', ' ', $state))),
                Tables\Columns\TextColumn::make('recipient'),
                Tables\Columns\TextColumn::make('failure_reason')
                    ->limit(50),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->label('Failed At'),
            ])
            ->paginated(false);
    }
}
