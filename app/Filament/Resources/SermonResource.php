<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SermonResource\Pages;
use App\Models\Sermon;
use BackedEnum;
use Filament\Actions;
use Filament\Forms\Components;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;

class SermonResource extends Resource
{
    protected static ?string $model = Sermon::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMicrophone;

    protected static ?int $navigationSort = 1;

    public static function getModelLabel(): string
    {
        return __('sermons.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('sermons.plural_label');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('sermons.navigation_group');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('sermons.section_info'))
                    ->description(__('sermons.section_info_desc'))
                    ->icon(Heroicon::OutlinedMicrophone)
                    ->schema([
                        Components\TextInput::make('title')
                            ->label(__('sermons.title'))
                            ->placeholder(__('sermons.title_placeholder'))
                            ->required()
                            ->maxLength(255),

                        Components\TextInput::make('slug')
                            ->label(__('sermons.slug'))
                            ->maxLength(255)
                            ->disabled()
                            ->dehydrated(),

                        Components\TextInput::make('speaker')
                            ->label(__('sermons.speaker'))
                            ->placeholder(__('sermons.speaker_placeholder'))
                            ->prefixIcon(Heroicon::OutlinedUser)
                            ->required()
                            ->maxLength(255),

                        Components\DatePicker::make('date')
                            ->label(__('sermons.date'))
                            ->required(),

                        Components\TextInput::make('duration')
                            ->label(__('sermons.duration_seconds'))
                            ->placeholder(__('sermons.duration_placeholder'))
                            ->numeric()
                            ->minValue(0),

                        Components\Select::make('series_id')
                            ->label(__('sermons.series'))
                            ->relationship('series', 'title')
                            ->searchable()
                            ->preload()
                            ->nullable(),

                        Components\Select::make('campus_id')
                            ->label(__('campuses.campus'))
                            ->relationship('campus', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                    ])
                    ->columns(2),

                Section::make(__('sermons.section_media'))
                    ->description(__('sermons.section_media_desc'))
                    ->icon(Heroicon::OutlinedFilm)
                    ->collapsible()
                    ->schema([
                        Components\TextInput::make('audio_url')
                            ->label(__('sermons.audio_url'))
                            ->placeholder(__('sermons.audio_url_placeholder'))
                            ->prefixIcon(Heroicon::OutlinedMusicalNote)
                            ->url()
                            ->maxLength(2048),

                        Components\TextInput::make('video_url')
                            ->label(__('sermons.video_url'))
                            ->placeholder(__('sermons.video_url_placeholder'))
                            ->prefixIcon(Heroicon::OutlinedVideoCamera)
                            ->url()
                            ->maxLength(2048),
                    ])
                    ->columns(2),

                Section::make(__('sermons.section_tags'))
                    ->description(__('sermons.section_tags_desc'))
                    ->icon(Heroicon::OutlinedTag)
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Components\TagsInput::make('tags')
                            ->label(__('sermons.tags')),

                        Components\MarkdownEditor::make('transcript')
                            ->label(__('sermons.transcript'))
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
                    ->label(__('sermons.title'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('speaker')
                    ->label(__('sermons.speaker'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('date')
                    ->label(__('sermons.date'))
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('series.title')
                    ->label(__('sermons.series'))
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('formatted_duration')
                    ->label(__('sermons.duration'))
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('sermons.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('series_id')
                    ->label(__('sermons.series'))
                    ->relationship('series', 'title'),

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
            'index' => Pages\ListSermons::route('/'),
            'create' => Pages\CreateSermon::route('/create'),
            'edit' => Pages\EditSermon::route('/{record}/edit'),
        ];
    }
}
