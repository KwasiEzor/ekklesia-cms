<?php

namespace App\Filament\Resources;

use BackedEnum;
use App\Filament\Resources\SermonResource\Pages;
use App\Models\Sermon;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Forms\Components;
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
                Components\Section::make()
                    ->schema([
                        Components\TextInput::make('title')
                            ->label(__('sermons.title'))
                            ->required()
                            ->maxLength(255),

                        Components\TextInput::make('slug')
                            ->label(__('sermons.slug'))
                            ->maxLength(255)
                            ->disabled()
                            ->dehydrated(),

                        Components\TextInput::make('speaker')
                            ->label(__('sermons.speaker'))
                            ->required()
                            ->maxLength(255),

                        Components\DatePicker::make('date')
                            ->label(__('sermons.date'))
                            ->required(),

                        Components\TextInput::make('duration')
                            ->label(__('sermons.duration_seconds'))
                            ->numeric()
                            ->minValue(0),

                        Components\Select::make('series_id')
                            ->label(__('sermons.series'))
                            ->relationship('series', 'title')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                    ])
                    ->columns(2),

                Components\Section::make('Média')
                    ->schema([
                        Components\TextInput::make('audio_url')
                            ->label(__('sermons.audio_url'))
                            ->url()
                            ->maxLength(2048),

                        Components\TextInput::make('video_url')
                            ->label(__('sermons.video_url'))
                            ->url()
                            ->maxLength(2048),
                    ])
                    ->columns(2),

                Components\Section::make()
                    ->schema([
                        Components\TagsInput::make('tags')
                            ->label(__('sermons.tags')),

                        Components\MarkdownEditor::make('transcript')
                            ->label(__('sermons.transcript'))
                            ->columnSpanFull(),
                    ]),
            ]);
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
            'index' => Pages\ListSermons::route('/'),
            'create' => Pages\CreateSermon::route('/create'),
            'edit' => Pages\EditSermon::route('/{record}/edit'),
        ];
    }
}
