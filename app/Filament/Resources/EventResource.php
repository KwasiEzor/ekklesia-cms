<?php

namespace App\Filament\Resources;

use BackedEnum;
use App\Filament\Resources\EventResource\Pages;
use App\Models\Event;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Forms\Components;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class EventResource extends Resource
{
    protected static ?string $model = Event::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static ?int $navigationSort = 2;

    public static function getModelLabel(): string
    {
        return __('events.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('events.plural_label');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('events.navigation_group');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Components\Section::make(__('events.details'))
                    ->schema([
                        Components\TextInput::make('title')
                            ->label(__('events.title'))
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($set, ?string $state) => $set('slug', Str::slug($state ?? ''))),

                        Components\TextInput::make('slug')
                            ->label(__('events.slug'))
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),

                        Components\DateTimePicker::make('start_at')
                            ->label(__('events.start_at'))
                            ->required(),

                        Components\DateTimePicker::make('end_at')
                            ->label(__('events.end_at'))
                            ->after('start_at'),

                        Components\TextInput::make('location')
                            ->label(__('events.location'))
                            ->maxLength(255),

                        Components\TextInput::make('capacity')
                            ->label(__('events.capacity'))
                            ->numeric()
                            ->minValue(1),
                    ])
                    ->columns(2),

                Components\Section::make(__('events.media'))
                    ->schema([
                        Components\TextInput::make('image')
                            ->label(__('events.image'))
                            ->url()
                            ->maxLength(2048),

                        Components\TextInput::make('registration_url')
                            ->label(__('events.registration_url'))
                            ->url()
                            ->maxLength(2048),
                    ])
                    ->columns(2),

                Components\Section::make()
                    ->schema([
                        Components\MarkdownEditor::make('description')
                            ->label(__('events.description'))
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label(__('events.title'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('start_at')
                    ->label(__('events.start_at'))
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('end_at')
                    ->label(__('events.end_at'))
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('location')
                    ->label(__('events.location'))
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('capacity')
                    ->label(__('events.capacity'))
                    ->numeric()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('events.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('start_at', 'desc')
            ->filters([
                Tables\Filters\Filter::make('upcoming')
                    ->label(__('events.upcoming'))
                    ->query(fn ($query) => $query->where('start_at', '>', now())),

                Tables\Filters\Filter::make('past')
                    ->label(__('events.past'))
                    ->query(fn ($query) => $query->where('start_at', '<=', now())),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEvents::route('/'),
            'create' => Pages\CreateEvent::route('/create'),
            'edit' => Pages\EditEvent::route('/{record}/edit'),
        ];
    }
}
