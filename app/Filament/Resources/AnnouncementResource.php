<?php

namespace App\Filament\Resources;

use BackedEnum;
use App\Filament\Resources\AnnouncementResource\Pages;
use App\Models\Announcement;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Forms\Components;
use Filament\Tables;
use Filament\Tables\Table;

class AnnouncementResource extends Resource
{
    protected static ?string $model = Announcement::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMegaphone;

    protected static ?int $navigationSort = 3;

    public static function getModelLabel(): string
    {
        return __('announcements.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('announcements.plural_label');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('announcements.navigation_group');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Components\Section::make(__('announcements.details'))
                    ->schema([
                        Components\TextInput::make('title')
                            ->label(__('announcements.title'))
                            ->required()
                            ->maxLength(255),

                        Components\TextInput::make('slug')
                            ->label(__('announcements.slug'))
                            ->maxLength(255)
                            ->disabled()
                            ->dehydrated(),

                        Components\DateTimePicker::make('published_at')
                            ->label(__('announcements.published_at')),

                        Components\DateTimePicker::make('expires_at')
                            ->label(__('announcements.expires_at'))
                            ->after('published_at'),

                        Components\Toggle::make('pinned')
                            ->label(__('announcements.pinned')),

                        Components\Select::make('target_group')
                            ->label(__('announcements.target_group'))
                            ->options([
                                'all' => __('announcements.groups.all'),
                                'youth' => __('announcements.groups.youth'),
                                'women' => __('announcements.groups.women'),
                                'men' => __('announcements.groups.men'),
                                'leaders' => __('announcements.groups.leaders'),
                            ]),
                    ])
                    ->columns(2),

                Components\Section::make()
                    ->schema([
                        Components\MarkdownEditor::make('body')
                            ->label(__('announcements.body'))
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label(__('announcements.title'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('published_at')
                    ->label(__('announcements.published_at'))
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('expires_at')
                    ->label(__('announcements.expires_at'))
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\IconColumn::make('pinned')
                    ->label(__('announcements.pinned'))
                    ->boolean()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('target_group')
                    ->label(__('announcements.target_group'))
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('announcements.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('published_at', 'desc')
            ->filters([
                Tables\Filters\Filter::make('active')
                    ->label(__('announcements.active'))
                    ->query(fn ($query) => $query
                        ->where('published_at', '<=', now())
                        ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))),

                Tables\Filters\Filter::make('expired')
                    ->label(__('announcements.expired'))
                    ->query(fn ($query) => $query->whereNotNull('expires_at')->where('expires_at', '<=', now())),

                Tables\Filters\TernaryFilter::make('pinned')
                    ->label(__('announcements.pinned')),
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
            'index' => Pages\ListAnnouncements::route('/'),
            'create' => Pages\CreateAnnouncement::route('/create'),
            'edit' => Pages\EditAnnouncement::route('/{record}/edit'),
        ];
    }
}
