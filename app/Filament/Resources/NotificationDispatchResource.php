<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NotificationDispatchResource\Pages;
use App\Models\NotificationDispatch;
use BackedEnum;
use Filament\Actions;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;

class NotificationDispatchResource extends Resource
{
    protected static ?string $model = NotificationDispatch::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBellAlert;

    protected static ?int $navigationSort = 1;

    public static function getModelLabel(): string
    {
        return __('notification_dispatches.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('notification_dispatches.plural_label');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('notification_dispatches.navigation_group');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('notification_dispatches.label'))
                    ->icon(Heroicon::OutlinedBellAlert)
                    ->schema([]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('channel')
                    ->label(__('notification_dispatches.channel'))
                    ->formatStateUsing(fn (string $state): string|array => __("notification_dispatches.channels.{$state}"))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'email' => 'info',
                        'sms' => 'success',
                        'whatsapp' => 'success',
                        'telegram' => 'primary',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->label(__('notification_dispatches.type'))
                    ->formatStateUsing(fn (string $state): string|array => __("notification_dispatches.types.{$state}"))
                    ->sortable(),

                Tables\Columns\TextColumn::make('recipient')
                    ->label(__('notification_dispatches.recipient'))
                    ->searchable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('subject')
                    ->label(__('notification_dispatches.subject'))
                    ->limit(40)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('status')
                    ->label(__('notification_dispatches.status'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string|array => __("notification_dispatches.statuses.{$state}"))
                    ->color(fn (string $state): string => match ($state) {
                        'sent' => 'success',
                        'delivered' => 'success',
                        'pending' => 'warning',
                        'failed' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('member.full_name')
                    ->label(__('notification_dispatches.member'))
                    ->default('-')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('sent_at')
                    ->label(__('notification_dispatches.sent_at'))
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('notification_dispatches.created_at'))
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('channel')
                    ->label(__('notification_dispatches.channel'))
                    ->options([
                        'email' => __('notification_dispatches.channels.email'),
                        'sms' => __('notification_dispatches.channels.sms'),
                        'whatsapp' => __('notification_dispatches.channels.whatsapp'),
                        'telegram' => __('notification_dispatches.channels.telegram'),
                    ]),

                Tables\Filters\SelectFilter::make('type')
                    ->label(__('notification_dispatches.type'))
                    ->options([
                        'welcome' => __('notification_dispatches.types.welcome'),
                        'giving_receipt' => __('notification_dispatches.types.giving_receipt'),
                        'event_reminder' => __('notification_dispatches.types.event_reminder'),
                        'announcement' => __('notification_dispatches.types.announcement'),
                        'birthday' => __('notification_dispatches.types.birthday'),
                    ]),

                Tables\Filters\SelectFilter::make('status')
                    ->label(__('notification_dispatches.status'))
                    ->options([
                        'pending' => __('notification_dispatches.statuses.pending'),
                        'sent' => __('notification_dispatches.statuses.sent'),
                        'delivered' => __('notification_dispatches.statuses.delivered'),
                        'failed' => __('notification_dispatches.statuses.failed'),
                    ]),
            ])
            ->actions([
                Actions\ViewAction::make()
                    ->iconButton(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNotificationDispatches::route('/'),
        ];
    }
}
