<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CampusResource\Pages;
use App\Models\Campus;
use BackedEnum;
use Filament\Actions;
use Filament\Forms\Components;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;

class CampusResource extends Resource
{
    protected static ?string $model = Campus::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static ?int $navigationSort = 1;

    public static function getModelLabel(): string
    {
        return __('campuses.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('campuses.plural_label');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('campuses.navigation_group');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('campuses.section_info'))
                    ->description(__('campuses.section_info_desc'))
                    ->icon(Heroicon::OutlinedBuildingOffice2)
                    ->schema([
                        Components\TextInput::make('name')
                            ->label(__('campuses.name'))
                            ->placeholder(__('campuses.name_placeholder'))
                            ->required()
                            ->maxLength(255),

                        Components\TextInput::make('slug')
                            ->label(__('campuses.slug'))
                            ->maxLength(255)
                            ->disabled()
                            ->dehydrated(),

                        Components\TextInput::make('pastor_name')
                            ->label(__('campuses.pastor_name'))
                            ->placeholder(__('campuses.pastor_name_placeholder'))
                            ->prefixIcon(Heroicon::OutlinedUser)
                            ->maxLength(255),

                        Components\TextInput::make('capacity')
                            ->label(__('campuses.capacity'))
                            ->placeholder(__('campuses.capacity_placeholder'))
                            ->numeric()
                            ->minValue(1),

                        Components\Toggle::make('is_main')
                            ->label(__('campuses.is_main'))
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make(__('campuses.section_contact'))
                    ->description(__('campuses.section_contact_desc'))
                    ->icon(Heroicon::OutlinedMapPin)
                    ->collapsible()
                    ->schema([
                        Components\Textarea::make('address')
                            ->label(__('campuses.address'))
                            ->placeholder(__('campuses.address_placeholder'))
                            ->rows(2)
                            ->columnSpanFull(),

                        Components\TextInput::make('city')
                            ->label(__('campuses.city'))
                            ->placeholder(__('campuses.city_placeholder'))
                            ->maxLength(255),

                        Components\TextInput::make('country')
                            ->label(__('campuses.country'))
                            ->placeholder(__('campuses.country_placeholder'))
                            ->maxLength(255),

                        Components\TextInput::make('phone')
                            ->label(__('campuses.phone'))
                            ->placeholder(__('campuses.phone_placeholder'))
                            ->tel()
                            ->maxLength(255),

                        Components\TextInput::make('email')
                            ->label(__('campuses.email'))
                            ->placeholder(__('campuses.email_placeholder'))
                            ->email()
                            ->maxLength(255),
                    ])
                    ->columns(2),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('campuses.name'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('city')
                    ->label(__('campuses.city'))
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('pastor_name')
                    ->label(__('campuses.pastor_name'))
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('capacity')
                    ->label(__('campuses.capacity'))
                    ->numeric()
                    ->toggleable(),

                Tables\Columns\IconColumn::make('is_main')
                    ->label(__('campuses.is_main'))
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('members_count')
                    ->label(__('campuses.members_count'))
                    ->counts('members')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('campuses.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name')
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
            'index' => Pages\ListCampuses::route('/'),
            'create' => Pages\CreateCampus::route('/create'),
            'edit' => Pages\EditCampus::route('/{record}/edit'),
        ];
    }
}
