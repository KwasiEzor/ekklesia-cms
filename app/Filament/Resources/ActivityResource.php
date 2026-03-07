<?php

namespace App\Filament\Resources;

use App\Models\Activity;
use BackedEnum;
use Filament\Actions;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ActivityResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static ?int $navigationSort = 100;

    public static function getNavigationGroup(): ?string
    {
        return __('activity.navigation_group');
    }

    public static function getModelLabel(): string
    {
        return __('activity.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('activity.plural_label');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('log_name')
                    ->label(__('activity.log_name'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'financial' => 'success',
                        'default' => 'gray',
                        default => 'info',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('event')
                    ->label(__('activity.event'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'created' => 'success',
                        'updated' => 'warning',
                        'deleted' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('description')
                    ->label(__('activity.description'))
                    ->searchable(),

                Tables\Columns\TextColumn::make('subject_type')
                    ->label(__('activity.subject'))
                    ->formatStateUsing(function ($state, $record): string {
                        $modelName = class_basename($state);

                        return "{$modelName} #{$record->subject_id}";
                    }),

                Tables\Columns\TextColumn::make('causer.name')
                    ->label(__('activity.causer'))
                    ->default('System'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('activity.created_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('log_name')
                    ->label(__('activity.log_name'))
                    ->options([
                        'financial' => __('activity.financial'),
                        'default' => __('activity.default'),
                    ]),
                SelectFilter::make('event')
                    ->label(__('activity.event'))
                    ->options([
                        'created' => __('activity.created'),
                        'updated' => __('activity.updated'),
                        'deleted' => __('activity.deleted'),
                    ]),
            ])
            ->actions([
                Actions\ViewAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ActivityResource\Pages\ListActivities::route('/'),
        ];
    }
}
