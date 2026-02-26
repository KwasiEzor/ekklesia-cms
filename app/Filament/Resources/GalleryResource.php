<?php

namespace App\Filament\Resources;

use BackedEnum;
use App\Filament\Resources\GalleryResource\Pages;
use App\Models\Gallery;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Forms\Components;
use Filament\Tables;
use Filament\Tables\Table;

class GalleryResource extends Resource
{
    protected static ?string $model = Gallery::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static ?int $navigationSort = 5;

    public static function getModelLabel(): string
    {
        return __('galleries.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('galleries.plural_label');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('galleries.navigation_group');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Components\Section::make(__('galleries.details'))
                    ->schema([
                        Components\TextInput::make('title')
                            ->label(__('galleries.title'))
                            ->required()
                            ->maxLength(255),

                        Components\Textarea::make('description')
                            ->label(__('galleries.description'))
                            ->rows(3)
                            ->maxLength(1000),

                        Components\Select::make('galleryable_type')
                            ->label(__('galleries.linked_to_type'))
                            ->options([
                                'App\\Models\\Event' => __('galleries.types.event'),
                                'App\\Models\\Member' => __('galleries.types.member'),
                            ])
                            ->reactive()
                            ->nullable(),

                        Components\Select::make('galleryable_id')
                            ->label(__('galleries.linked_to'))
                            ->options(function (callable $get) {
                                $type = $get('galleryable_type');

                                if (! $type || ! class_exists($type)) {
                                    return [];
                                }

                                return $type::query()
                                    ->limit(50)
                                    ->get()
                                    ->pluck('title', 'id')
                                    ->when($type === 'App\\Models\\Member', fn ($c) => $type::query()->limit(50)->get()->mapWithKeys(fn ($m) => [$m->id => $m->full_name]))
                                    ->toArray();
                            })
                            ->searchable()
                            ->nullable()
                            ->visible(fn (callable $get) => filled($get('galleryable_type'))),
                    ])
                    ->columns(2),

                Components\Section::make(__('galleries.photos'))
                    ->schema([
                        Components\FileUpload::make('photos')
                            ->label(__('galleries.photos'))
                            ->image()
                            ->multiple()
                            ->reorderable()
                            ->directory('galleries')
                            ->maxSize(5120)
                            ->maxFiles(50)
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif']),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('cover')
                    ->label(__('galleries.cover'))
                    ->state(fn (Gallery $record) => $record->cover_url)
                    ->circular()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('title')
                    ->label(__('galleries.title'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('description')
                    ->label(__('galleries.description'))
                    ->limit(50)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('photo_count')
                    ->label(__('galleries.photo_count'))
                    ->state(fn (Gallery $record) => $record->photo_count)
                    ->badge(),

                Tables\Columns\TextColumn::make('galleryable_type')
                    ->label(__('galleries.linked_to_type'))
                    ->formatStateUsing(fn (?string $state) => $state ? class_basename($state) : '—')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('galleries.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
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
            'index' => Pages\ListGalleries::route('/'),
            'create' => Pages\CreateGallery::route('/create'),
            'edit' => Pages\EditGallery::route('/{record}/edit'),
        ];
    }
}
