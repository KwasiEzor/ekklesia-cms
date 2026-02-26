<?php

namespace App\Filament\Resources;

use BackedEnum;
use App\Filament\Resources\PageResource\Pages;
use App\Models\Page;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Forms\Components;
use Filament\Tables;
use Filament\Tables\Table;

class PageResource extends Resource
{
    protected static ?string $model = Page::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?int $navigationSort = 4;

    public static function getModelLabel(): string
    {
        return __('pages.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('pages.plural_label');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('pages.navigation_group');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Components\Section::make(__('pages.details'))
                    ->schema([
                        Components\TextInput::make('title')
                            ->label(__('pages.title'))
                            ->required()
                            ->maxLength(255),

                        Components\TextInput::make('slug')
                            ->label(__('pages.slug'))
                            ->maxLength(255)
                            ->disabled()
                            ->dehydrated(),
                    ])
                    ->columns(2),

                Components\Section::make(__('pages.content_blocks'))
                    ->schema([
                        Components\Builder::make('content_blocks')
                            ->label(__('pages.content_blocks'))
                            ->blocks([
                                Components\Builder\Block::make('heading')
                                    ->label(__('pages.blocks.heading'))
                                    ->schema([
                                        Components\Select::make('level')
                                            ->label(__('pages.blocks.heading_level'))
                                            ->options([
                                                'h2' => 'H2',
                                                'h3' => 'H3',
                                                'h4' => 'H4',
                                            ])
                                            ->default('h2')
                                            ->required(),

                                        Components\TextInput::make('content')
                                            ->label(__('pages.blocks.heading_content'))
                                            ->required()
                                            ->maxLength(255),
                                    ])
                                    ->columns(2),

                                Components\Builder\Block::make('rich_text')
                                    ->label(__('pages.blocks.rich_text'))
                                    ->schema([
                                        Components\MarkdownEditor::make('body')
                                            ->label(__('pages.blocks.rich_text_body'))
                                            ->required(),
                                    ]),

                                Components\Builder\Block::make('image')
                                    ->label(__('pages.blocks.image'))
                                    ->schema([
                                        Components\TextInput::make('url')
                                            ->label(__('pages.blocks.image_url'))
                                            ->url()
                                            ->required()
                                            ->maxLength(2048),

                                        Components\TextInput::make('alt')
                                            ->label(__('pages.blocks.image_alt'))
                                            ->maxLength(255),

                                        Components\TextInput::make('caption')
                                            ->label(__('pages.blocks.image_caption'))
                                            ->maxLength(255),
                                    ]),

                                Components\Builder\Block::make('video')
                                    ->label(__('pages.blocks.video'))
                                    ->schema([
                                        Components\TextInput::make('url')
                                            ->label(__('pages.blocks.video_url'))
                                            ->url()
                                            ->required()
                                            ->maxLength(2048),

                                        Components\TextInput::make('caption')
                                            ->label(__('pages.blocks.video_caption'))
                                            ->maxLength(255),
                                    ]),

                                Components\Builder\Block::make('call_to_action')
                                    ->label(__('pages.blocks.call_to_action'))
                                    ->schema([
                                        Components\TextInput::make('label')
                                            ->label(__('pages.blocks.cta_label'))
                                            ->required()
                                            ->maxLength(255),

                                        Components\TextInput::make('url')
                                            ->label(__('pages.blocks.cta_url'))
                                            ->required()
                                            ->maxLength(2048),

                                        Components\Select::make('style')
                                            ->label(__('pages.blocks.cta_style'))
                                            ->options([
                                                'primary' => __('pages.blocks.cta_primary'),
                                                'secondary' => __('pages.blocks.cta_secondary'),
                                            ])
                                            ->default('primary')
                                            ->required(),
                                    ])
                                    ->columns(3),

                                Components\Builder\Block::make('quote')
                                    ->label(__('pages.blocks.quote'))
                                    ->schema([
                                        Components\Textarea::make('text')
                                            ->label(__('pages.blocks.quote_text'))
                                            ->required()
                                            ->rows(3),

                                        Components\TextInput::make('attribution')
                                            ->label(__('pages.blocks.quote_attribution'))
                                            ->maxLength(255),
                                    ]),
                            ])
                            ->columnSpanFull()
                            ->collapsible()
                            ->reorderable(),
                    ]),

                Components\Section::make(__('pages.seo_section'))
                    ->schema([
                        Components\TextInput::make('seo_title')
                            ->label(__('pages.seo_title'))
                            ->maxLength(255),

                        Components\TextInput::make('seo_description')
                            ->label(__('pages.seo_description'))
                            ->maxLength(255),

                        Components\DateTimePicker::make('published_at')
                            ->label(__('pages.published_at')),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label(__('pages.title'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('slug')
                    ->label(__('pages.slug'))
                    ->toggleable(),

                Tables\Columns\TextColumn::make('published_at')
                    ->label(__('pages.published_at'))
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->badge()
                    ->color(fn ($record) => $record->is_published ? 'success' : 'warning')
                    ->formatStateUsing(fn ($state, $record) => $record->is_published ? __('pages.published') : __('pages.draft')),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('pages.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('published_at', 'desc')
            ->filters([
                Tables\Filters\Filter::make('published')
                    ->label(__('pages.published'))
                    ->query(fn ($query) => $query->whereNotNull('published_at')->where('published_at', '<=', now())),

                Tables\Filters\Filter::make('draft')
                    ->label(__('pages.draft'))
                    ->query(fn ($query) => $query->whereNull('published_at')),
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
            'index' => Pages\ListPages::route('/'),
            'create' => Pages\CreatePage::route('/create'),
            'edit' => Pages\EditPage::route('/{record}/edit'),
        ];
    }
}
