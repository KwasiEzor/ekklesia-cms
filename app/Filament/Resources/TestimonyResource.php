<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TestimonyResource\Pages;
use App\Models\Member;
use App\Models\Testimony;
use BackedEnum;
use Filament\Actions;
use Filament\Forms\Components;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;

class TestimonyResource extends Resource
{
    protected static ?string $model = Testimony::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedStar;

    protected static ?int $navigationSort = 3;

    public static function getModelLabel(): string
    {
        return __('testimonies.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('testimonies.plural_label');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('testimonies.navigation_group');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('testimonies.section_content'))
                    ->description(__('testimonies.section_content_desc'))
                    ->icon(Heroicon::OutlinedStar)
                    ->schema([
                        Components\TextInput::make('title')
                            ->label(__('testimonies.title'))
                            ->required()
                            ->maxLength(255),

                        Components\Select::make('member_id')
                            ->label(__('testimonies.member'))
                            ->relationship('member')
                            ->getOptionLabelFromRecordUsing(fn (Member $record) => $record->full_name)
                            ->searchable(['first_name', 'last_name'])
                            ->preload()
                            ->nullable(),

                        Components\Select::make('category')
                            ->label(__('testimonies.category'))
                            ->options([
                                'healing' => __('testimonies.categories.healing'),
                                'provision' => __('testimonies.categories.provision'),
                                'deliverance' => __('testimonies.categories.deliverance'),
                                'conversion' => __('testimonies.categories.conversion'),
                                'family_restoration' => __('testimonies.categories.family_restoration'),
                                'other' => __('testimonies.categories.other'),
                            ])
                            ->required(),

                        Components\Toggle::make('is_anonymous')
                            ->label(__('testimonies.is_anonymous')),

                        Components\RichEditor::make('content')
                            ->label(__('testimonies.content'))
                            ->required()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make(__('testimonies.section_moderation'))
                    ->description(__('testimonies.section_moderation_desc'))
                    ->icon(Heroicon::OutlinedShieldCheck)
                    ->collapsible()
                    ->schema([
                        Components\Select::make('status')
                            ->label(__('testimonies.status'))
                            ->options([
                                'submitted' => __('testimonies.statuses.submitted'),
                                'approved' => __('testimonies.statuses.approved'),
                                'rejected' => __('testimonies.statuses.rejected'),
                            ])
                            ->default('submitted')
                            ->required(),

                        Components\Toggle::make('is_featured')
                            ->label(__('testimonies.is_featured')),

                        Components\Textarea::make('rejection_reason')
                            ->label(__('testimonies.rejection_reason'))
                            ->rows(2)
                            ->columnSpanFull(),
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
                    ->label(__('testimonies.title'))
                    ->searchable()
                    ->sortable()
                    ->limit(40),

                Tables\Columns\TextColumn::make('member.full_name')
                    ->label(__('testimonies.member'))
                    ->default(__('testimonies.anonymous_label'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('category')
                    ->label(__('testimonies.category'))
                    ->formatStateUsing(fn ($state): string|array|null => __("testimonies.categories.{$state}"))
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label(__('testimonies.status'))
                    ->formatStateUsing(fn ($state): string|array|null => __("testimonies.statuses.{$state}"))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'submitted' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_featured')
                    ->label(__('testimonies.is_featured'))
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('testimonies.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('testimonies.status'))
                    ->options([
                        'submitted' => __('testimonies.statuses.submitted'),
                        'approved' => __('testimonies.statuses.approved'),
                        'rejected' => __('testimonies.statuses.rejected'),
                    ]),

                Tables\Filters\SelectFilter::make('category')
                    ->label(__('testimonies.category'))
                    ->options([
                        'healing' => __('testimonies.categories.healing'),
                        'provision' => __('testimonies.categories.provision'),
                        'deliverance' => __('testimonies.categories.deliverance'),
                        'conversion' => __('testimonies.categories.conversion'),
                        'family_restoration' => __('testimonies.categories.family_restoration'),
                        'other' => __('testimonies.categories.other'),
                    ]),

                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label(__('testimonies.is_featured')),
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
            'index' => Pages\ListTestimonies::route('/'),
            'create' => Pages\CreateTestimony::route('/create'),
            'edit' => Pages\EditTestimony::route('/{record}/edit'),
        ];
    }
}
