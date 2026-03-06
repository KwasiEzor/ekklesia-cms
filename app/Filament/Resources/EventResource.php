<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EventResource\Pages;
use App\Models\Event;
use BackedEnum;
use Filament\Actions;
use Filament\Forms\Components;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;

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
                Section::make(__('events.section_details'))
                    ->description(__('events.section_details_desc'))
                    ->icon(Heroicon::OutlinedCalendarDays)
                    ->schema([
                        Components\TextInput::make('title')
                            ->label(__('events.title'))
                            ->placeholder(__('events.title_placeholder'))
                            ->required()
                            ->maxLength(255),

                        Components\TextInput::make('slug')
                            ->label(__('events.slug'))
                            ->maxLength(255)
                            ->disabled()
                            ->dehydrated(),

                        Components\DateTimePicker::make('start_at')
                            ->label(__('events.start_at'))
                            ->required(),

                        Components\DateTimePicker::make('end_at')
                            ->label(__('events.end_at'))
                            ->after('start_at'),

                        Components\TextInput::make('location')
                            ->label(__('events.location'))
                            ->placeholder(__('events.location_placeholder'))
                            ->prefixIcon(Heroicon::OutlinedMapPin)
                            ->maxLength(255),

                        Components\TextInput::make('capacity')
                            ->label(__('events.capacity'))
                            ->placeholder(__('events.capacity_placeholder'))
                            ->numeric()
                            ->minValue(1),

                        Components\Select::make('campus_id')
                            ->label(__('campuses.campus'))
                            ->relationship('campus', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                    ])
                    ->columns(2),

                Section::make(__('events.section_links'))
                    ->description(__('events.section_links_desc'))
                    ->icon(Heroicon::OutlinedLink)
                    ->collapsible()
                    ->schema([
                        Components\TextInput::make('image')
                            ->label(__('events.image'))
                            ->placeholder(__('events.image_placeholder'))
                            ->prefixIcon(Heroicon::OutlinedPhoto)
                            ->url()
                            ->maxLength(2048),

                        Components\TextInput::make('registration_url')
                            ->label(__('events.registration_url'))
                            ->placeholder(__('events.registration_url_placeholder'))
                            ->prefixIcon(Heroicon::OutlinedLink)
                            ->url()
                            ->maxLength(2048),
                    ])
                    ->columns(2),

                Section::make(__('events.section_description'))
                    ->description(__('events.section_description_desc'))
                    ->icon(Heroicon::OutlinedDocumentText)
                    ->schema([
                        Components\MarkdownEditor::make('description')
                            ->label(__('events.description'))
                            ->columnSpanFull(),
                    ]),
            ])
            ->columns(1);
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

                Tables\Filters\SelectFilter::make('campus_id')
                    ->label(__('campuses.campus'))
                    ->relationship('campus', 'name'),
            ])
            ->actions([
                Actions\ViewAction::make()
                    ->iconButton(),
                Actions\EditAction::make()
                    ->iconButton(),
                Actions\DeleteAction::make()
                    ->iconButton(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
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
