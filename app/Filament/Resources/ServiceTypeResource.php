<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceTypeResource\Pages;
use App\Models\ServiceType;
use BackedEnum;
use Filament\Actions;
use Filament\Forms\Components;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;

class ServiceTypeResource extends Resource
{
    protected static ?string $model = ServiceType::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    protected static ?int $navigationSort = 12;

    public static function getModelLabel(): string
    {
        return __('service_types.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('service_types.plural_label');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('service_types.navigation_group');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('service_types.section_details'))
                    ->description(__('service_types.section_details_desc'))
                    ->icon(Heroicon::OutlinedClock)
                    ->schema([
                        Components\TextInput::make('name')
                            ->label(__('service_types.name'))
                            ->placeholder(__('service_types.name_placeholder'))
                            ->required()
                            ->maxLength(255),

                        Components\TextInput::make('slug')
                            ->label(__('service_types.slug'))
                            ->maxLength(255)
                            ->disabled()
                            ->dehydrated(),

                        Components\Toggle::make('is_active')
                            ->label(__('service_types.is_active'))
                            ->default(true),

                        Components\TextInput::make('sort_order')
                            ->label(__('service_types.sort_order'))
                            ->numeric()
                            ->default(0),

                        Components\Select::make('campus_id')
                            ->label(__('campuses.campus'))
                            ->relationship('campus', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                    ])
                    ->columns(2),

                Section::make(__('service_types.section_schedule'))
                    ->description(__('service_types.section_schedule_desc'))
                    ->icon(Heroicon::OutlinedCalendarDays)
                    ->collapsible()
                    ->schema([
                        Components\Select::make('day_of_week')
                            ->label(__('service_types.day_of_week'))
                            ->options([
                                'monday' => __('service_types.monday'),
                                'tuesday' => __('service_types.tuesday'),
                                'wednesday' => __('service_types.wednesday'),
                                'thursday' => __('service_types.thursday'),
                                'friday' => __('service_types.friday'),
                                'saturday' => __('service_types.saturday'),
                                'sunday' => __('service_types.sunday'),
                            ])
                            ->nullable(),

                        Components\TimePicker::make('default_start_time')
                            ->label(__('service_types.default_start_time'))
                            ->nullable(),

                        Components\TextInput::make('expected_duration_minutes')
                            ->label(__('service_types.expected_duration_minutes'))
                            ->placeholder(__('service_types.duration_placeholder'))
                            ->numeric()
                            ->minValue(1),
                    ])
                    ->columns(3),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->emptyStateIcon(Heroicon::OutlinedClock)
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('service_types.name'))
                    ->icon(Heroicon::OutlinedClock)
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('day_of_week')
                    ->label(__('service_types.day_of_week'))
                    ->icon(Heroicon::OutlinedCalendarDays)
                    ->formatStateUsing(fn (?string $state) => $state ? __("service_types.{$state}") : '—')
                    ->sortable(),

                Tables\Columns\TextColumn::make('default_start_time')
                    ->label(__('service_types.default_start_time'))
                    ->icon(Heroicon::OutlinedClock)
                    ->time('H:i')
                    ->toggleable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('service_types.is_active'))
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('campus.name')
                    ->label(__('campuses.campus'))
                    ->icon(Heroicon::OutlinedBuildingOffice)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('service_types.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('sort_order')
            ->actions([
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
            'index' => Pages\ListServiceTypes::route('/'),
            'create' => Pages\CreateServiceType::route('/create'),
            'edit' => Pages\EditServiceType::route('/{record}/edit'),
        ];
    }
}
