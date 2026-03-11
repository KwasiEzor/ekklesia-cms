<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HouseholdResource\Pages;
use App\Models\Household;
use BackedEnum;
use Filament\Actions;
use Filament\Forms\Components;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;

class HouseholdResource extends Resource
{
    protected static ?string $model = Household::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHomeModern;

    protected static ?int $navigationSort = 10;

    public static function getModelLabel(): string
    {
        return __('households.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('households.plural_label');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('households.navigation_group');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('households.section_details'))
                    ->description(__('households.section_details_desc'))
                    ->icon(Heroicon::OutlinedHomeModern)
                    ->schema([
                        Components\TextInput::make('name')
                            ->label(__('households.name'))
                            ->placeholder(__('households.name_placeholder'))
                            ->required()
                            ->maxLength(255),

                        Components\TextInput::make('slug')
                            ->label(__('households.slug'))
                            ->maxLength(255)
                            ->disabled()
                            ->dehydrated(),

                        Components\TextInput::make('address')
                            ->label(__('households.address'))
                            ->placeholder(__('households.address_placeholder'))
                            ->prefixIcon(Heroicon::OutlinedMapPin)
                            ->maxLength(500),

                        Components\TextInput::make('city')
                            ->label(__('households.city'))
                            ->placeholder(__('households.city_placeholder'))
                            ->prefixIcon(Heroicon::OutlinedBuildingOffice2)
                            ->maxLength(255),

                        Components\TextInput::make('phone')
                            ->label(__('households.phone'))
                            ->placeholder(__('households.phone_placeholder'))
                            ->prefixIcon(Heroicon::OutlinedPhone)
                            ->tel()
                            ->maxLength(50),
                    ])
                    ->columns(2),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->emptyStateIcon(Heroicon::OutlinedHomeModern)
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('households.name'))
                    ->icon(Heroicon::OutlinedHomeModern)
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('members_count')
                    ->label(__('households.members_count'))
                    ->icon(Heroicon::OutlinedUsers)
                    ->counts('members')
                    ->sortable(),

                Tables\Columns\TextColumn::make('city')
                    ->label(__('households.city'))
                    ->icon(Heroicon::OutlinedBuildingOffice2)
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('phone')
                    ->label(__('households.phone'))
                    ->icon(Heroicon::OutlinedPhone)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('address')
                    ->label(__('households.address'))
                    ->icon(Heroicon::OutlinedMapPin)
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('households.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name')
            ->actions([
                Actions\ViewAction::make()
                    ->icon(Heroicon::OutlinedEye)
                    ->iconButton(),
                Actions\EditAction::make()
                    ->icon(Heroicon::OutlinedPencil)
                    ->iconButton(),
                Actions\DeleteAction::make()
                    ->icon(Heroicon::OutlinedTrash)
                    ->iconButton(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHouseholds::route('/'),
            'create' => Pages\CreateHousehold::route('/create'),
            'edit' => Pages\EditHousehold::route('/{record}/edit'),
        ];
    }
}
