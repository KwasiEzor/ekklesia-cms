<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BulkMessageResource\Pages;
use App\Models\BulkMessage;
use BackedEnum;
use Filament\Actions;
use Filament\Forms\Components;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;

class BulkMessageResource extends Resource
{
    protected static ?string $model = BulkMessage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static ?int $navigationSort = 4;

    public static function getModelLabel(): string
    {
        return __('bulk_messages.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('bulk_messages.plural_label');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('bulk_messages.navigation_group');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('bulk_messages.section_content'))
                    ->description(__('bulk_messages.section_content_desc'))
                    ->icon(Heroicon::OutlinedChatBubbleLeftRight)
                    ->schema([
                        Components\TextInput::make('title')
                            ->label(__('bulk_messages.title'))
                            ->required()
                            ->maxLength(255),

                        Components\Textarea::make('body')
                            ->label(__('bulk_messages.body'))
                            ->required()
                            ->maxLength(1600)
                            ->rows(4)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make(__('bulk_messages.section_delivery'))
                    ->description(__('bulk_messages.section_delivery_desc'))
                    ->icon(Heroicon::OutlinedPaperAirplane)
                    ->schema([
                        Components\Select::make('channel')
                            ->label(__('bulk_messages.channel'))
                            ->options([
                                'sms' => __('bulk_messages.channels.sms'),
                                'email' => __('bulk_messages.channels.email'),
                                'whatsapp' => __('bulk_messages.channels.whatsapp'),
                            ])
                            ->default('sms')
                            ->required(),

                        Components\Select::make('target_type')
                            ->label(__('bulk_messages.target_type'))
                            ->options([
                                'all' => __('bulk_messages.targets.all'),
                                'cell_group' => __('bulk_messages.targets.cell_group'),
                                'campus' => __('bulk_messages.targets.campus'),
                                'status' => __('bulk_messages.targets.status'),
                            ])
                            ->default('all')
                            ->required()
                            ->reactive(),

                        Components\Select::make('target_id')
                            ->label(__('bulk_messages.target_id'))
                            ->visible(fn (callable $get): bool => in_array($get('target_type'), ['cell_group', 'campus', 'status']))
                            ->options(fn (callable $get) => match ($get('target_type')) {
                                'cell_group' => \App\Models\CellGroup::pluck('name', 'id'),
                                'campus' => \App\Models\Campus::pluck('name', 'id'),
                                'status' => [
                                    'active' => __('members.statuses.active'),
                                    'inactive' => __('members.statuses.inactive'),
                                    'visiting' => __('members.statuses.visiting'),
                                ],
                                default => [],
                            })
                            ->searchable(),

                        Components\DateTimePicker::make('scheduled_at')
                            ->label(__('bulk_messages.scheduled_at'))
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
                Tables\Columns\TextColumn::make('title')
                    ->label(__('bulk_messages.title'))
                    ->searchable()
                    ->sortable()
                    ->limit(40),

                Tables\Columns\TextColumn::make('channel')
                    ->label(__('bulk_messages.channel'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'sms' => 'info',
                        'email' => 'primary',
                        'whatsapp' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => __("bulk_messages.channels.{$state}")),

                Tables\Columns\TextColumn::make('target_type')
                    ->label(__('bulk_messages.target_type'))
                    ->formatStateUsing(fn (string $state): string => __("bulk_messages.targets.{$state}"))
                    ->toggleable(),

                Tables\Columns\TextColumn::make('status')
                    ->label(__('bulk_messages.status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'scheduled' => 'warning',
                        'sending' => 'info',
                        'sent' => 'success',
                        'failed' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => __("bulk_messages.statuses.{$state}")),

                Tables\Columns\TextColumn::make('recipient_count')
                    ->label(__('bulk_messages.recipient_count'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('sent_count')
                    ->label(__('bulk_messages.sent_count'))
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('bulk_messages.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('bulk_messages.status'))
                    ->options([
                        'draft' => __('bulk_messages.statuses.draft'),
                        'scheduled' => __('bulk_messages.statuses.scheduled'),
                        'sending' => __('bulk_messages.statuses.sending'),
                        'sent' => __('bulk_messages.statuses.sent'),
                        'failed' => __('bulk_messages.statuses.failed'),
                    ]),

                Tables\Filters\SelectFilter::make('channel')
                    ->label(__('bulk_messages.channel'))
                    ->options([
                        'sms' => __('bulk_messages.channels.sms'),
                        'email' => __('bulk_messages.channels.email'),
                        'whatsapp' => __('bulk_messages.channels.whatsapp'),
                    ]),
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
            'index' => Pages\ListBulkMessages::route('/'),
            'create' => Pages\CreateBulkMessage::route('/create'),
            'edit' => Pages\EditBulkMessage::route('/{record}/edit'),
        ];
    }
}
