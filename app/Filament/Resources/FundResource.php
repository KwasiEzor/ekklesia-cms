<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FundResource\Pages;
use App\Models\Fund;
use BackedEnum;
use Filament\Actions;
use Filament\Forms\Components;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;

class FundResource extends Resource
{
    protected static ?string $model = Fund::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedWallet;

    protected static ?int $navigationSort = 2;

    public static function getModelLabel(): string
    {
        return __('funds.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('funds.plural_label');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('funds.navigation_group');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('funds.section_info'))
                    ->description(__('funds.section_info_desc'))
                    ->icon(Heroicon::OutlinedWallet)
                    ->schema([
                        Components\TextInput::make('name')
                            ->label(__('funds.name'))
                            ->required()
                            ->maxLength(255),

                        Components\Select::make('type')
                            ->label(__('funds.type'))
                            ->options([
                                'tithe' => __('funds.types.tithe'),
                                'offering' => __('funds.types.offering'),
                                'building' => __('funds.types.building'),
                                'missions' => __('funds.types.missions'),
                                'benevolence' => __('funds.types.benevolence'),
                                'other' => __('funds.types.other'),
                            ])
                            ->required(),

                        Components\Textarea::make('description')
                            ->label(__('funds.description'))
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make(__('funds.section_settings'))
                    ->icon(Heroicon::OutlinedCog6Tooth)
                    ->collapsible()
                    ->schema([
                        Components\Toggle::make('is_active')
                            ->label(__('funds.is_active'))
                            ->default(true),

                        Components\TextInput::make('sort_order')
                            ->label(__('funds.sort_order'))
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
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
                    ->label(__('funds.name'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->label(__('funds.type'))
                    ->formatStateUsing(fn ($state): string|array|null => __("funds.types.{$state}"))
                    ->badge()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('funds.is_active'))
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('giving_records_count')
                    ->label(__('funds.donations_count'))
                    ->counts('givingRecords')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_raised')
                    ->label(__('funds.total_raised'))
                    ->getStateUsing(fn (Fund $record): string => number_format((float) $record->givingRecords()->sum('amount'), 2, ',', ' ').' XOF')
                    ->sortable(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label(__('funds.sort_order'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('funds.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('sort_order')
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label(__('funds.type'))
                    ->options([
                        'tithe' => __('funds.types.tithe'),
                        'offering' => __('funds.types.offering'),
                        'building' => __('funds.types.building'),
                        'missions' => __('funds.types.missions'),
                        'benevolence' => __('funds.types.benevolence'),
                        'other' => __('funds.types.other'),
                    ]),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label(__('funds.is_active')),
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
            'index' => Pages\ListFunds::route('/'),
            'create' => Pages\CreateFund::route('/create'),
            'edit' => Pages\EditFund::route('/{record}/edit'),
        ];
    }
}
