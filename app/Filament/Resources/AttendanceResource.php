<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AttendanceResource\Pages;
use App\Models\Attendance;
use BackedEnum;
use Filament\Actions;
use Filament\Forms\Components;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;

class AttendanceResource extends Resource
{
    protected static ?string $model = Attendance::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static ?int $navigationSort = 11;

    public static function getModelLabel(): string
    {
        return __('attendances.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('attendances.plural_label');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('attendances.navigation_group');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('attendances.section_details'))
                    ->description(__('attendances.section_details_desc'))
                    ->icon(Heroicon::OutlinedClipboardDocumentCheck)
                    ->schema([
                        Components\Select::make('member_id')
                            ->label(__('attendances.member'))
                            ->placeholder(__('attendances.member_placeholder'))
                            ->relationship('member', 'first_name')
                            ->getOptionLabelFromRecordUsing(fn ($record): string => "{$record->first_name} {$record->last_name}")
                            ->searchable(['first_name', 'last_name'])
                            ->preload()
                            ->required(),

                        Components\Select::make('service_type_id')
                            ->label(__('attendances.service_type'))
                            ->placeholder(__('attendances.service_type_placeholder'))
                            ->relationship('serviceType', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),

                        Components\Select::make('event_id')
                            ->label(__('attendances.event'))
                            ->relationship('event', 'title')
                            ->searchable()
                            ->preload()
                            ->nullable(),

                        Components\Select::make('cell_group_id')
                            ->label(__('attendances.cell_group'))
                            ->relationship('cellGroup', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),

                        Components\DatePicker::make('date')
                            ->label(__('attendances.date'))
                            ->required()
                            ->default(now()),

                        Components\Select::make('campus_id')
                            ->label(__('campuses.campus'))
                            ->relationship('campus', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                    ])
                    ->columns(2),

                Section::make(__('attendances.section_additional'))
                    ->description(__('attendances.section_additional_desc'))
                    ->icon(Heroicon::OutlinedInformationCircle)
                    ->collapsible()
                    ->schema([
                        Components\TimePicker::make('checked_in_at')
                            ->label(__('attendances.checked_in_at'))
                            ->nullable(),

                        Components\TimePicker::make('checked_out_at')
                            ->label(__('attendances.checked_out_at'))
                            ->nullable(),

                        Components\Select::make('check_in_method')
                            ->label(__('attendances.check_in_method'))
                            ->options([
                                'manual' => __('attendances.method_manual'),
                                'qr_code' => __('attendances.method_qr_code'),
                                'self_check_in' => __('attendances.method_self_check_in'),
                            ])
                            ->default('manual'),

                        Components\Toggle::make('is_first_time')
                            ->label(__('attendances.is_first_time'))
                            ->default(false),

                        Components\Textarea::make('notes')
                            ->label(__('attendances.notes'))
                            ->placeholder(__('attendances.notes_placeholder'))
                            ->columnSpanFull()
                            ->rows(2),
                    ])
                    ->columns(2),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->emptyStateIcon(Heroicon::OutlinedClipboardDocumentCheck)
            ->columns([
                Tables\Columns\TextColumn::make('member.first_name')
                    ->label(__('attendances.member'))
                    ->icon(Heroicon::OutlinedUser)
                    ->formatStateUsing(fn ($record) => $record->member?->full_name)
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(),

                Tables\Columns\TextColumn::make('date')
                    ->label(__('attendances.date'))
                    ->icon(Heroicon::OutlinedCalendarDays)
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('serviceType.name')
                    ->label(__('attendances.service_type'))
                    ->icon(Heroicon::OutlinedClock)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('checked_in_at')
                    ->label(__('attendances.checked_in_at'))
                    ->time('H:i')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('check_in_method')
                    ->label(__('attendances.check_in_method'))
                    ->formatStateUsing(fn (string $state): string|array|null => __("attendances.method_{$state}"))
                    ->toggleable(),

                Tables\Columns\IconColumn::make('is_first_time')
                    ->label(__('attendances.is_first_time'))
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('campus.name')
                    ->label(__('campuses.campus'))
                    ->icon(Heroicon::OutlinedBuildingOffice)
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('attendances.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('date', 'desc')
            ->filters([
                Tables\Filters\Filter::make('first_timers')
                    ->label(__('attendances.filter_first_timers'))
                    ->query(fn ($query) => $query->where('is_first_time', true)),

                Tables\Filters\SelectFilter::make('service_type_id')
                    ->label(__('attendances.service_type'))
                    ->relationship('serviceType', 'name'),

                Tables\Filters\SelectFilter::make('campus_id')
                    ->label(__('campuses.campus'))
                    ->relationship('campus', 'name'),

                Tables\Filters\Filter::make('date_range')
                    ->form([
                        Components\DatePicker::make('date_from')
                            ->label(__('attendances.filter_date_from')),
                        Components\DatePicker::make('date_to')
                            ->label(__('attendances.filter_date_to')),
                    ])
                    ->query(fn ($query, array $data) => $query
                        ->when($data['date_from'], fn ($q, $date) => $q->whereDate('date', '>=', $date))
                        ->when($data['date_to'], fn ($q, $date) => $q->whereDate('date', '<=', $date))),
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
            'index' => Pages\ListAttendances::route('/'),
            'create' => Pages\CreateAttendance::route('/create'),
            'edit' => Pages\EditAttendance::route('/{record}/edit'),
        ];
    }
}
