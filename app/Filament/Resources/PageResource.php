<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PageResource\Pages;
use App\Models\Page;
use BackedEnum;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Forms\Components;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Actions as SchemaActions;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;

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
                SchemaActions::make([
                    Action::make('preview')
                        ->label(__('common.actions.view').' (Preview)')
                        ->icon(Heroicon::OutlinedEye)
                        ->color('info')
                        ->modalHeading(__('common.actions.view').' : Preview')
                        ->modalWidth('full')
                        ->modalContent(fn (Components\Builder|array $state, $get): View => view(
                            'filament.pages.preview-page',
                            [
                                'title' => $get('title') ?? 'Untitled Page',
                                'blocks' => $get('content_blocks') ?? [],
                            ]
                        ))
                        ->modalSubmitAction(false)
                        ->modalCancelActionLabel('Close'),
                ])->alignment('right'),

                Section::make(__('pages.section_details'))
                    ->description(__('pages.section_details_desc'))
                    ->icon(Heroicon::OutlinedDocumentText)
                    ->schema([
                        Components\TextInput::make('title')
                            ->label(__('pages.title'))
                            ->placeholder(__('pages.title_placeholder'))
                            ->required()
                            ->maxLength(255),

                        Components\TextInput::make('slug')
                            ->label(__('pages.slug'))
                            ->maxLength(255)
                            ->disabled()
                            ->dehydrated(),
                    ])
                    ->columns(2),

                Section::make(__('pages.section_blocks'))
                    ->description(__('pages.section_blocks_desc'))
                    ->icon(Heroicon::OutlinedRectangleGroup)
                    ->schema([
                        Components\Builder::make('content_blocks')
                            ->label(__('pages.content_blocks'))
                            ->blocks([
                                Components\Builder\Block::make('hero')
                                    ->label(__('pages.blocks.hero'))
                                    ->icon(Heroicon::OutlinedSparkles)
                                    ->schema([
                                        Components\TextInput::make('title')
                                            ->label(__('pages.blocks.hero_title'))
                                            ->required()
                                            ->maxLength(255),

                                        Components\Textarea::make('subtitle')
                                            ->label(__('pages.blocks.hero_subtitle'))
                                            ->rows(2)
                                            ->maxLength(500),

                                        Components\TextInput::make('image_url')
                                            ->label(__('pages.blocks.hero_image'))
                                            ->url()
                                            ->required(),

                                        Group::make([
                                            Components\TextInput::make('cta_label')
                                                ->label(__('pages.blocks.hero_cta_label'))
                                                ->maxLength(255),

                                            Components\TextInput::make('cta_url')
                                                ->label(__('pages.blocks.hero_cta_url'))
                                                ->url(),
                                        ])->columns(2),
                                    ]),

                                Components\Builder\Block::make('heading')
                                    ->label(__('pages.blocks.heading'))
                                    ->icon(Heroicon::OutlinedBars3)
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
                                    ->icon(Heroicon::OutlinedBars3BottomLeft)
                                    ->schema([
                                        Components\MarkdownEditor::make('body')
                                            ->label(__('pages.blocks.rich_text_body'))
                                            ->required(),
                                    ]),

                                Components\Builder\Block::make('features')
                                    ->label(__('pages.blocks.features'))
                                    ->icon(Heroicon::OutlinedSquaresPlus)
                                    ->schema([
                                        Components\Repeater::make('items')
                                            ->label(__('pages.blocks.features_items'))
                                            ->schema([
                                                Components\TextInput::make('icon')
                                                    ->label(__('pages.blocks.features_icon'))
                                                    ->placeholder('E.g. heart, star, home')
                                                    ->required(),

                                                Components\TextInput::make('title')
                                                    ->label(__('pages.blocks.features_title'))
                                                    ->required()
                                                    ->maxLength(255),

                                                Components\Textarea::make('description')
                                                    ->label(__('pages.blocks.features_desc'))
                                                    ->required()
                                                    ->rows(2),
                                            ])
                                            ->columns(1)
                                            ->grid(3)
                                            ->itemLabel(fn (array $state): ?string => $state['title'] ?? null),
                                    ]),

                                Components\Builder\Block::make('image')
                                    ->label(__('pages.blocks.image'))
                                    ->icon(Heroicon::OutlinedPhoto)
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

                                Components\Builder\Block::make('faq')
                                    ->label(__('pages.blocks.faq'))
                                    ->icon(Heroicon::OutlinedQuestionMarkCircle)
                                    ->schema([
                                        Components\Repeater::make('items')
                                            ->label(__('pages.blocks.faq_items'))
                                            ->schema([
                                                Components\TextInput::make('question')
                                                    ->label(__('pages.blocks.faq_question'))
                                                    ->required()
                                                    ->maxLength(255),

                                                Components\Textarea::make('answer')
                                                    ->label(__('pages.blocks.faq_answer'))
                                                    ->required()
                                                    ->rows(2),
                                            ])
                                            ->itemLabel(fn (array $state): ?string => $state['question'] ?? null),
                                    ]),

                                Components\Builder\Block::make('video')
                                    ->label(__('pages.blocks.video'))
                                    ->icon(Heroicon::OutlinedVideoCamera)
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
                                    ->icon(Heroicon::OutlinedCursorArrowRays)
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
                                    ->icon(Heroicon::OutlinedChatBubbleBottomCenterText)
                                    ->schema([
                                        Components\Textarea::make('text')
                                            ->label(__('pages.blocks.quote_text'))
                                            ->required()
                                            ->rows(3),

                                        Components\TextInput::make('attribution')
                                            ->label(__('pages.blocks.quote_attribution'))
                                            ->maxLength(255),
                                    ]),

                                Components\Builder\Block::make('testimonials')
                                    ->label(__('pages.blocks.testimonials'))
                                    ->icon(Heroicon::OutlinedUserGroup)
                                    ->schema([
                                        Components\Repeater::make('items')
                                            ->label(__('pages.blocks.testimonials_items'))
                                            ->schema([
                                                Components\TextInput::make('name')
                                                    ->label(__('pages.blocks.testimonials_name'))
                                                    ->required()
                                                    ->maxLength(255),

                                                Components\TextInput::make('role')
                                                    ->label(__('pages.blocks.testimonials_role'))
                                                    ->maxLength(255),

                                                Components\Textarea::make('content')
                                                    ->label(__('pages.blocks.testimonials_content'))
                                                    ->required()
                                                    ->rows(3),

                                                Components\TextInput::make('avatar_url')
                                                    ->label(__('pages.blocks.testimonials_avatar'))
                                                    ->url(),
                                            ])
                                            ->grid(2)
                                            ->itemLabel(fn (array $state): ?string => $state['name'] ?? null),
                                    ]),

                                Components\Builder\Block::make('contact_form')
                                    ->label(__('pages.blocks.contact_form'))
                                    ->icon(Heroicon::OutlinedEnvelope)
                                    ->schema([
                                        Components\TextInput::make('title')
                                            ->label(__('pages.blocks.contact_form_title'))
                                            ->default('Get in Touch')
                                            ->required()
                                            ->maxLength(255),

                                        Components\Textarea::make('description')
                                            ->label(__('pages.blocks.contact_form_desc'))
                                            ->default(__('pages.blocks.contact_form_placeholder'))
                                            ->rows(2),

                                        Components\TextInput::make('email_to')
                                            ->label('Target Email')
                                            ->placeholder('Defaults to church email')
                                            ->email(),
                                    ]),
                            ])
                            ->columnSpanFull()
                            ->collapsible()
                            ->collapsed()
                            ->reorderable()
                            ->blockIcons(),
                    ]),

                Section::make(__('pages.section_seo'))
                    ->description(__('pages.section_seo_desc'))
                    ->icon(Heroicon::OutlinedMagnifyingGlass)
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Components\TextInput::make('seo_title')
                            ->label(__('pages.seo_title'))
                            ->placeholder(__('pages.seo_title_placeholder'))
                            ->maxLength(255),

                        Components\TextInput::make('seo_description')
                            ->label(__('pages.seo_description'))
                            ->placeholder(__('pages.seo_description_placeholder'))
                            ->maxLength(255),

                        Components\DateTimePicker::make('published_at')
                            ->label(__('pages.published_at')),
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
                    ->color(fn ($record): string => $record->is_published ? 'success' : 'warning')
                    ->formatStateUsing(fn ($state, $record): string|array|null => $record->is_published ? __('pages.published') : __('pages.draft')),

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
                Actions\ViewAction::make()
                    ->iconButton(),
                Actions\EditAction::make()
                    ->iconButton(),
                Actions\DeleteAction::make()
                    ->iconButton(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
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
