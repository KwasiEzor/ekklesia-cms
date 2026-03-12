<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PrayerRequestResource\Pages;
use App\Models\PrayerRequest;
use BackedEnum;
use Filament\Actions;
use Filament\Forms\Components;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;

class PrayerRequestResource extends Resource
{
    protected static ?string $model = PrayerRequest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHandRaised;

    protected static ?int $navigationSort = 20;

    public static function getModelLabel(): string
    {
        return __('prayer_requests.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('prayer_requests.plural_label');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('prayer_requests.navigation_group');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('prayer_requests.section_details'))
                    ->description(__('prayer_requests.section_details_desc'))
                    ->icon(Heroicon::OutlinedHandRaised)
                    ->schema([
                        Components\Select::make('member_id')
                            ->label(__('prayer_requests.member'))
                            ->relationship('member', 'first_name')
                            ->getOptionLabelFromRecordUsing(fn ($record): string => "{$record->first_name} {$record->last_name}")
                            ->searchable(['first_name', 'last_name'])
                            ->preload()
                            ->required(),

                        Components\Select::make('type')
                            ->label(__('prayer_requests.type'))
                            ->options([
                                'prayer' => __('prayer_requests.type_prayer'),
                                'praise' => __('prayer_requests.type_praise'),
                                'testimony' => __('prayer_requests.type_testimony'),
                            ])
                            ->default('prayer')
                            ->required(),

                        Components\TextInput::make('title')
                            ->label(__('prayer_requests.title'))
                            ->placeholder(__('prayer_requests.title_placeholder'))
                            ->required()
                            ->maxLength(255),

                        Components\Select::make('category')
                            ->label(__('prayer_requests.category'))
                            ->options([
                                'guérison' => __('prayer_requests.category_healing'),
                                'provision' => __('prayer_requests.category_provision'),
                                'famille' => __('prayer_requests.category_family'),
                                'travail' => __('prayer_requests.category_work'),
                                'études' => __('prayer_requests.category_studies'),
                                'délivrance' => __('prayer_requests.category_deliverance'),
                                'mariage' => __('prayer_requests.category_marriage'),
                                'voyage' => __('prayer_requests.category_travel'),
                            ])
                            ->nullable(),

                        Components\Textarea::make('content')
                            ->label(__('prayer_requests.content'))
                            ->placeholder(__('prayer_requests.content_placeholder'))
                            ->required()
                            ->rows(4)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make(__('prayer_requests.section_moderation'))
                    ->description(__('prayer_requests.section_moderation_desc'))
                    ->icon(Heroicon::OutlinedShieldCheck)
                    ->collapsible()
                    ->schema([
                        Components\Select::make('visibility')
                            ->label(__('prayer_requests.visibility'))
                            ->options([
                                'public' => __('prayer_requests.visibility_public'),
                                'group' => __('prayer_requests.visibility_group'),
                                'confidential' => __('prayer_requests.visibility_confidential'),
                            ])
                            ->default('public')
                            ->required(),

                        Components\Select::make('status')
                            ->label(__('prayer_requests.status'))
                            ->options([
                                'active' => __('prayer_requests.status_active'),
                                'answered' => __('prayer_requests.status_answered'),
                                'closed' => __('prayer_requests.status_closed'),
                            ])
                            ->default('active'),

                        Components\Toggle::make('is_anonymous')
                            ->label(__('prayer_requests.is_anonymous'))
                            ->default(false),

                        Components\Toggle::make('is_featured')
                            ->label(__('prayer_requests.is_featured'))
                            ->default(false),

                        Components\Select::make('cell_group_id')
                            ->label(__('attendances.cell_group'))
                            ->relationship('cellGroup', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                    ])
                    ->columns(2),

                Section::make(__('prayer_requests.section_answer'))
                    ->description(__('prayer_requests.section_answer_desc'))
                    ->icon(Heroicon::OutlinedCheckCircle)
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Components\DateTimePicker::make('answered_at')
                            ->label(__('prayer_requests.answered_at'))
                            ->nullable(),

                        Components\Textarea::make('testimony')
                            ->label(__('prayer_requests.testimony'))
                            ->placeholder(__('prayer_requests.testimony_placeholder'))
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->emptyStateIcon(Heroicon::OutlinedHandRaised)
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label(__('prayer_requests.title'))
                    ->icon(Heroicon::OutlinedHandRaised)
                    ->searchable()
                    ->sortable()
                    ->limit(40),

                Tables\Columns\TextColumn::make('type')
                    ->label(__('prayer_requests.type'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string|array|null => __("prayer_requests.type_{$state}"))
                    ->color(fn (string $state): string => match ($state) {
                        'prayer' => 'primary',
                        'praise' => 'success',
                        'testimony' => 'warning',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('member.first_name')
                    ->label(__('prayer_requests.member'))
                    ->formatStateUsing(fn ($record) => $record->is_anonymous ? __('prayer_requests.is_anonymous') : $record->member?->full_name)
                    ->icon(Heroicon::OutlinedUser)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('category')
                    ->label(__('prayer_requests.category'))
                    ->toggleable(),

                Tables\Columns\TextColumn::make('status')
                    ->label(__('prayer_requests.status'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string|array|null => __("prayer_requests.status_{$state}"))
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'primary',
                        'answered' => 'success',
                        'closed' => 'gray',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('commitments_count')
                    ->label(__('prayer_requests.prayer_count'))
                    ->icon(Heroicon::OutlinedHeart)
                    ->counts('commitments')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_featured')
                    ->label(__('prayer_requests.is_featured'))
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('prayer_requests.created_at'))
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label(__('prayer_requests.type'))
                    ->options([
                        'prayer' => __('prayer_requests.type_prayer'),
                        'praise' => __('prayer_requests.type_praise'),
                        'testimony' => __('prayer_requests.type_testimony'),
                    ]),

                Tables\Filters\SelectFilter::make('status')
                    ->label(__('prayer_requests.status'))
                    ->options([
                        'active' => __('prayer_requests.status_active'),
                        'answered' => __('prayer_requests.status_answered'),
                        'closed' => __('prayer_requests.status_closed'),
                    ]),

                Tables\Filters\Filter::make('featured')
                    ->label(__('prayer_requests.is_featured'))
                    ->query(fn ($query) => $query->where('is_featured', true)),
            ])
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
            'index' => Pages\ListPrayerRequests::route('/'),
            'create' => Pages\CreatePrayerRequest::route('/create'),
            'edit' => Pages\EditPrayerRequest::route('/{record}/edit'),
        ];
    }
}
