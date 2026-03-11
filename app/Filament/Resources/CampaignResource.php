<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CampaignResource\Pages;
use App\Models\Campaign;
use BackedEnum;
use Filament\Actions;
use Filament\Forms\Components;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;

class CampaignResource extends Resource
{
    protected static ?string $model = Campaign::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFlag;

    protected static ?int $navigationSort = 3;

    public static function getModelLabel(): string
    {
        return __('campaigns.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('campaigns.plural_label');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('campaigns.navigation_group');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('campaigns.section_info'))
                    ->description(__('campaigns.section_info_desc'))
                    ->icon(Heroicon::OutlinedFlag)
                    ->schema([
                        Components\TextInput::make('name')
                            ->label(__('campaigns.name'))
                            ->required()
                            ->maxLength(255),

                        Components\Select::make('fund_id')
                            ->label(__('campaigns.fund'))
                            ->relationship('fund', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),

                        Components\Textarea::make('description')
                            ->label(__('campaigns.description'))
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make(__('campaigns.section_goal'))
                    ->description(__('campaigns.section_goal_desc'))
                    ->icon(Heroicon::OutlinedChartBar)
                    ->schema([
                        Components\TextInput::make('goal_amount')
                            ->label(__('campaigns.goal_amount'))
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->nullable(),

                        Components\Select::make('currency')
                            ->label(__('campaigns.currency'))
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

                        Components\DatePicker::make('start_date')
                            ->label(__('campaigns.start_date'))
                            ->required()
                            ->default(now()),

                        Components\DatePicker::make('end_date')
                            ->label(__('campaigns.end_date'))
                            ->nullable()
                            ->afterOrEqual('start_date'),
                    ])
                    ->columns(2),

                Section::make(__('campaigns.section_status'))
                    ->icon(Heroicon::OutlinedCog6Tooth)
                    ->collapsible()
                    ->schema([
                        Components\Select::make('status')
                            ->label(__('campaigns.status'))
                            ->options([
                                'draft' => __('campaigns.statuses.draft'),
                                'active' => __('campaigns.statuses.active'),
                                'completed' => __('campaigns.statuses.completed'),
                                'cancelled' => __('campaigns.statuses.cancelled'),
                            ])
                            ->default('active')
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
                Tables\Columns\TextColumn::make('name')
                    ->label(__('campaigns.name'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('fund.name')
                    ->label(__('campaigns.fund'))
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('goal_amount')
                    ->label(__('campaigns.goal_amount'))
                    ->formatStateUsing(fn ($state, $record): string => $state ? number_format((float) $state, 0, ',', ' ').' '.$record->currency : '—')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_raised')
                    ->label(__('campaigns.total_raised'))
                    ->getStateUsing(fn (Campaign $record): string => number_format((float) $record->givingRecords()->sum('amount'), 2, ',', ' ').' '.$record->currency)
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label(__('campaigns.status'))
                    ->formatStateUsing(fn ($state): string|array|null => __("campaigns.statuses.{$state}"))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'active' => 'success',
                        'completed' => 'info',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('start_date')
                    ->label(__('campaigns.start_date'))
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('end_date')
                    ->label(__('campaigns.end_date'))
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('giving_records_count')
                    ->label(__('campaigns.donations_count'))
                    ->counts('givingRecords')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('campaigns.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('start_date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('campaigns.status'))
                    ->options([
                        'draft' => __('campaigns.statuses.draft'),
                        'active' => __('campaigns.statuses.active'),
                        'completed' => __('campaigns.statuses.completed'),
                        'cancelled' => __('campaigns.statuses.cancelled'),
                    ]),

                Tables\Filters\SelectFilter::make('fund_id')
                    ->label(__('campaigns.fund'))
                    ->relationship('fund', 'name'),
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
            'index' => Pages\ListCampaigns::route('/'),
            'create' => Pages\CreateCampaign::route('/create'),
            'edit' => Pages\EditCampaign::route('/{record}/edit'),
        ];
    }
}
