<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GivingRecordResource\Pages;
use App\Models\GivingRecord;
use App\Models\Member;
use BackedEnum;
use Filament\Actions;
use Filament\Forms\Components;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;

class GivingRecordResource extends Resource
{
    protected static ?string $model = GivingRecord::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static ?int $navigationSort = 1;

    public static function getModelLabel(): string
    {
        return __('giving_records.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('giving_records.plural_label');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('giving_records.navigation_group');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('giving_records.section_info'))
                    ->description(__('giving_records.section_info_desc'))
                    ->icon(Heroicon::OutlinedBanknotes)
                    ->schema([
                        Components\Select::make('member_id')
                            ->label(__('giving_records.member'))
                            ->relationship('member')
                            ->getOptionLabelFromRecordUsing(fn (Member $record) => $record->full_name)
                            ->searchable(['first_name', 'last_name'])
                            ->preload()
                            ->nullable()
                            ->helperText(__('giving_records.member_help')),

                        Components\TextInput::make('amount')
                            ->label(__('giving_records.amount'))
                            ->placeholder(__('giving_records.amount_placeholder'))
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01),

                        Components\Select::make('currency')
                            ->label(__('giving_records.currency'))
                            ->options([
                                'XOF' => 'XOF — Franc CFA (BCEAO)',
                                'XAF' => 'XAF — Franc CFA (BEAC)',
                                'EUR' => 'EUR — Euro',
                                'USD' => 'USD — Dollar US',
                                'GBP' => 'GBP — Livre sterling',
                                'CAD' => 'CAD — Dollar canadien',
                            ])
                            ->default('XOF')
                            ->required(),

                        Components\DatePicker::make('date')
                            ->label(__('giving_records.date'))
                            ->required()
                            ->default(now()),

                        Components\Select::make('method')
                            ->label(__('giving_records.method'))
                            ->options([
                                'mobile_money' => __('giving_records.methods.mobile_money'),
                                'cash' => __('giving_records.methods.cash'),
                                'bank_transfer' => __('giving_records.methods.bank_transfer'),
                                'card' => __('giving_records.methods.card'),
                            ])
                            ->required(),
                    ])
                    ->columns(2),

                Section::make(__('giving_records.section_tracking'))
                    ->description(__('giving_records.section_tracking_desc'))
                    ->icon(Heroicon::OutlinedReceiptPercent)
                    ->collapsible()
                    ->schema([
                        Components\TextInput::make('reference')
                            ->label(__('giving_records.reference'))
                            ->placeholder(__('giving_records.reference_placeholder'))
                            ->maxLength(255),

                        Components\TextInput::make('campaign_id')
                            ->label(__('giving_records.campaign'))
                            ->placeholder(__('giving_records.campaign_placeholder'))
                            ->maxLength(255),

                        Components\Select::make('campus_id')
                            ->label(__('campuses.campus'))
                            ->relationship('campus', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                    ])
                    ->columns(2),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('member.full_name')
                    ->label(__('giving_records.member'))
                    ->default(__('giving_records.anonymous'))
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(),

                Tables\Columns\TextColumn::make('amount')
                    ->label(__('giving_records.amount'))
                    ->formatStateUsing(fn ($state, $record): string => number_format((float) $state, 2, ',', ' ').' '.$record->currency)
                    ->sortable(),

                Tables\Columns\TextColumn::make('method')
                    ->label(__('giving_records.method'))
                    ->formatStateUsing(fn ($state): string|array|null => __("giving_records.methods.{$state}"))
                    ->sortable(),

                Tables\Columns\TextColumn::make('date')
                    ->label(__('giving_records.date'))
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('reference')
                    ->label(__('giving_records.reference'))
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('giving_records.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('method')
                    ->label(__('giving_records.method'))
                    ->options([
                        'mobile_money' => __('giving_records.methods.mobile_money'),
                        'cash' => __('giving_records.methods.cash'),
                        'bank_transfer' => __('giving_records.methods.bank_transfer'),
                        'card' => __('giving_records.methods.card'),
                    ]),

                Tables\Filters\SelectFilter::make('currency')
                    ->label(__('giving_records.currency'))
                    ->options([
                        'XOF' => 'XOF',
                        'XAF' => 'XAF',
                        'EUR' => 'EUR',
                        'USD' => 'USD',
                    ]),

                Tables\Filters\Filter::make('anonymous')
                    ->label(__('giving_records.anonymous'))
                    ->query(fn ($query) => $query->whereNull('member_id')),

                Tables\Filters\SelectFilter::make('campus_id')
                    ->label(__('campuses.campus'))
                    ->relationship('campus', 'name'),
            ])
            ->actions([
                Actions\ViewAction::make()
                    ->iconButton(),
            ])
            ->bulkActions([
                // No bulk actions to ensure immutability
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGivingRecords::route('/'),
            'create' => Pages\CreateGivingRecord::route('/create'),
        ];
    }
}
