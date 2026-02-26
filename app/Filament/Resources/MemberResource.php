<?php

namespace App\Filament\Resources;

use BackedEnum;
use App\Filament\Resources\MemberResource\Pages;
use App\Models\Member;
use App\States\MemberStatus\MemberStatus;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Forms\Components;
use Filament\Tables;
use Filament\Tables\Table;

class MemberResource extends Resource
{
    protected static ?string $model = Member::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?int $navigationSort = 1;

    public static function getModelLabel(): string
    {
        return __('members.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('members.plural_label');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('members.navigation_group');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Components\Section::make(__('members.avatar'))
                    ->schema([
                        Components\FileUpload::make('avatar')
                            ->label(__('members.avatar'))
                            ->image()
                            ->avatar()
                            ->imageEditor()
                            ->directory('members/avatars')
                            ->maxSize(2048)
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp']),
                    ])
                    ->collapsible(),

                Components\Section::make(__('members.details'))
                    ->schema([
                        Components\TextInput::make('first_name')
                            ->label(__('members.first_name'))
                            ->required()
                            ->maxLength(255),

                        Components\TextInput::make('last_name')
                            ->label(__('members.last_name'))
                            ->required()
                            ->maxLength(255),

                        Components\TextInput::make('email')
                            ->label(__('members.email'))
                            ->email()
                            ->maxLength(255),

                        Components\TextInput::make('phone')
                            ->label(__('members.phone'))
                            ->tel()
                            ->maxLength(255),

                        Components\DatePicker::make('baptism_date')
                            ->label(__('members.baptism_date')),

                        Components\Select::make('cell_group_id')
                            ->label(__('members.cell_group'))
                            ->relationship('cellGroup', 'name')
                            ->searchable()
                            ->preload(),

                        Components\Select::make('status')
                            ->label(__('members.status'))
                            ->options([
                                'active' => __('members.statuses.active'),
                                'inactive' => __('members.statuses.inactive'),
                                'visiting' => __('members.statuses.visiting'),
                                'transferred' => __('members.statuses.transferred'),
                            ])
                            ->default('active')
                            ->required(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('avatar_url')
                    ->label(__('members.avatar'))
                    ->circular()
                    ->defaultImageUrl(fn (Member $record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->full_name) . '&background=random')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('full_name')
                    ->label(__('members.full_name'))
                    ->state(fn (Member $record) => $record->full_name)
                    ->searchable(query: function ($query, string $search) {
                        $query->where('first_name', 'ilike', "%{$search}%")
                            ->orWhere('last_name', 'ilike', "%{$search}%");
                    })
                    ->sortable(query: fn ($query, string $direction) => $query->orderBy('last_name', $direction)),

                Tables\Columns\TextColumn::make('email')
                    ->label(__('members.email'))
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('phone')
                    ->label(__('members.phone'))
                    ->toggleable(),

                Tables\Columns\TextColumn::make('status')
                    ->label(__('members.status'))
                    ->badge()
                    ->formatStateUsing(fn (MemberStatus $state): string => $state->label())
                    ->color(fn (MemberStatus $state): string => $state->color()),

                Tables\Columns\TextColumn::make('cellGroup.name')
                    ->label(__('members.cell_group'))
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('members.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('last_name', 'asc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('members.status'))
                    ->options([
                        'active' => __('members.statuses.active'),
                        'inactive' => __('members.statuses.inactive'),
                        'visiting' => __('members.statuses.visiting'),
                        'transferred' => __('members.statuses.transferred'),
                    ]),

                Tables\Filters\SelectFilter::make('cell_group_id')
                    ->label(__('members.cell_group'))
                    ->relationship('cellGroup', 'name'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMembers::route('/'),
            'create' => Pages\CreateMember::route('/create'),
            'edit' => Pages\EditMember::route('/{record}/edit'),
        ];
    }
}
