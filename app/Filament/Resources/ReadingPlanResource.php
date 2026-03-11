<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReadingPlanResource\Pages;
use App\Models\ReadingPlan;
use BackedEnum;
use Filament\Actions;
use Filament\Forms\Components;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;

class ReadingPlanResource extends Resource
{
    protected static ?string $model = ReadingPlan::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookOpen;

    protected static ?int $navigationSort = 2;

    public static function getModelLabel(): string
    {
        return __('reading_plans.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('reading_plans.plural_label');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('reading_plans.navigation_group');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('reading_plans.section_info'))
                    ->description(__('reading_plans.section_info_desc'))
                    ->icon(Heroicon::OutlinedBookOpen)
                    ->schema([
                        Components\TextInput::make('title')
                            ->label(__('reading_plans.title'))
                            ->required()
                            ->maxLength(255),

                        Components\Textarea::make('description')
                            ->label(__('reading_plans.description'))
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make(__('reading_plans.section_settings'))
                    ->description(__('reading_plans.section_settings_desc'))
                    ->icon(Heroicon::OutlinedCog6Tooth)
                    ->collapsible()
                    ->schema([
                        Components\TextInput::make('duration_days')
                            ->label(__('reading_plans.duration_days'))
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->maxValue(365)
                            ->default(30),

                        Components\TextInput::make('grace_period_days')
                            ->label(__('reading_plans.grace_period_days'))
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(7)
                            ->default(1),

                        Components\Toggle::make('is_active')
                            ->label(__('reading_plans.is_active'))
                            ->default(true),
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
                    ->label(__('reading_plans.title'))
                    ->searchable()
                    ->sortable()
                    ->limit(40),

                Tables\Columns\TextColumn::make('duration_days')
                    ->label(__('reading_plans.duration_days'))
                    ->suffix(' jours')
                    ->sortable(),

                Tables\Columns\TextColumn::make('grace_period_days')
                    ->label(__('reading_plans.grace_period_days'))
                    ->suffix(' jour(s)')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('reading_plans.is_active'))
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('days_count')
                    ->label(__('reading_plans.days_count'))
                    ->counts('days')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('reading_plans.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label(__('reading_plans.is_active')),
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
            'index' => Pages\ListReadingPlans::route('/'),
            'create' => Pages\CreateReadingPlan::route('/create'),
            'edit' => Pages\EditReadingPlan::route('/{record}/edit'),
        ];
    }
}
