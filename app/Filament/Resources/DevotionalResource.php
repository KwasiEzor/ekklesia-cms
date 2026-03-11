<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DevotionalResource\Pages;
use App\Models\Devotional;
use BackedEnum;
use Filament\Actions;
use Filament\Forms\Components;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;

class DevotionalResource extends Resource
{
    protected static ?string $model = Devotional::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookOpen;

    protected static ?int $navigationSort = 1;

    public static function getModelLabel(): string
    {
        return __('devotionals.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('devotionals.plural_label');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('devotionals.navigation_group');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('devotionals.section_content'))
                    ->description(__('devotionals.section_content_desc'))
                    ->icon(Heroicon::OutlinedBookOpen)
                    ->schema([
                        Components\TextInput::make('title')
                            ->label(__('devotionals.title'))
                            ->required()
                            ->maxLength(255),

                        Components\Select::make('series_id')
                            ->label(__('devotionals.series'))
                            ->relationship('series', 'title')
                            ->searchable()
                            ->preload()
                            ->nullable(),

                        Components\TextInput::make('verse_reference')
                            ->label(__('devotionals.verse_reference'))
                            ->placeholder(__('devotionals.verse_reference_placeholder'))
                            ->required()
                            ->maxLength(255),

                        Components\TextInput::make('author')
                            ->label(__('devotionals.author'))
                            ->nullable()
                            ->maxLength(255),

                        Components\Textarea::make('verse_text')
                            ->label(__('devotionals.verse_text'))
                            ->rows(3)
                            ->columnSpanFull(),

                        Components\RichEditor::make('reflection')
                            ->label(__('devotionals.reflection'))
                            ->required()
                            ->columnSpanFull(),

                        Components\Textarea::make('prayer_point')
                            ->label(__('devotionals.prayer_point'))
                            ->rows(3)
                            ->columnSpanFull(),

                        Components\Textarea::make('application')
                            ->label(__('devotionals.application'))
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make(__('devotionals.section_scheduling'))
                    ->description(__('devotionals.section_scheduling_desc'))
                    ->icon(Heroicon::OutlinedCalendar)
                    ->collapsible()
                    ->schema([
                        Components\DatePicker::make('scheduled_date')
                            ->label(__('devotionals.scheduled_date'))
                            ->required()
                            ->default(now()),

                        Components\Select::make('status')
                            ->label(__('devotionals.status'))
                            ->options([
                                'draft' => __('devotionals.statuses.draft'),
                                'scheduled' => __('devotionals.statuses.scheduled'),
                                'published' => __('devotionals.statuses.published'),
                            ])
                            ->default('draft')
                            ->required(),
                    ])
                    ->columns(2),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label(__('devotionals.title'))
                    ->searchable()
                    ->sortable()
                    ->limit(40),

                Tables\Columns\TextColumn::make('verse_reference')
                    ->label(__('devotionals.verse_reference'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('series.title')
                    ->label(__('devotionals.series'))
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('scheduled_date')
                    ->label(__('devotionals.scheduled_date'))
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label(__('devotionals.status'))
                    ->formatStateUsing(fn ($state): string|array|null => __("devotionals.statuses.{$state}"))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'scheduled' => 'warning',
                        'published' => 'success',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('author')
                    ->label(__('devotionals.author'))
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('devotionals.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('scheduled_date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('devotionals.status'))
                    ->options([
                        'draft' => __('devotionals.statuses.draft'),
                        'scheduled' => __('devotionals.statuses.scheduled'),
                        'published' => __('devotionals.statuses.published'),
                    ]),

                Tables\Filters\SelectFilter::make('series_id')
                    ->label(__('devotionals.series'))
                    ->relationship('series', 'title'),
            ])
            ->actions([
                Actions\EditAction::make()
                    ->iconButton(),
                Actions\DeleteAction::make()
                    ->iconButton(),
            ])
            ->bulkActions([
                Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDevotionals::route('/'),
            'create' => Pages\CreateDevotional::route('/create'),
            'edit' => Pages\EditDevotional::route('/{record}/edit'),
        ];
    }
}
