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
                                        Components\Repeater::make('slides')
                                            ->label(__('pages.blocks.hero_slides'))
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
                                            ])
                                            ->minItems(1)
                                            ->defaultItems(1)
                                            ->reorderable()
                                            ->collapsible(),

                                        Group::make([
                                            Components\Toggle::make('is_carousel')
                                                ->label(__('pages.blocks.is_carousel'))
                                                ->default(false)
                                                ->live(),

                                            Components\TextInput::make('autoplay_speed')
                                                ->label(__('pages.blocks.autoplay_speed'))
                                                ->numeric()
                                                ->default(5000)
                                                ->visible(fn (callable $get) => $get('is_carousel')),

                                            Components\Select::make('transition_type')
                                                ->label(__('pages.blocks.hero_transition_type'))
                                                ->options([
                                                    'fade' => __('pages.blocks.hero_transition_fade'),
                                                    'slide' => __('pages.blocks.hero_transition_slide'),
                                                ])
                                                ->default('fade')
                                                ->visible(fn (callable $get) => $get('is_carousel')),
                                        ])->columns(3),
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

                                        Group::make([
                                            Components\Toggle::make('is_carousel')
                                                ->label(__('pages.blocks.is_carousel'))
                                                ->default(false)
                                                ->live(),

                                            Components\TextInput::make('autoplay_speed')
                                                ->label(__('pages.blocks.autoplay_speed'))
                                                ->numeric()
                                                ->default(5000)
                                                ->visible(fn (callable $get) => $get('is_carousel')),
                                        ])->columns(2),
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

                                Components\Builder\Block::make('sermon_feed')
                                    ->label(__('pages.blocks.sermon_feed'))
                                    ->icon(Heroicon::OutlinedMicrophone)
                                    ->schema([
                                        Components\TextInput::make('title')
                                            ->label(__('pages.blocks.sermon_feed_title'))
                                            ->default('Latest Sermons')
                                            ->required()
                                            ->maxLength(255),

                                        Components\Select::make('limit')
                                            ->label(__('pages.blocks.sermon_feed_limit'))
                                            ->options([
                                                3 => '3 Sermons',
                                                6 => '6 Sermons',
                                                9 => '9 Sermons',
                                            ])
                                            ->default(3),

                                        Components\Select::make('series_id')
                                            ->label(__('pages.blocks.sermon_feed_series'))
                                            ->options(fn () => \App\Models\SermonSeries::pluck('title', 'id'))
                                            ->placeholder('All Series')
                                            ->searchable(),

                                        Components\Select::make('view_style')
                                            ->label(__('pages.blocks.sermon_feed_style'))
                                            ->options([
                                                'grid' => 'Grid View',
                                                'list' => 'List View',
                                                'featured' => 'Featured (1 large + others)',
                                            ])
                                            ->default('grid'),

                                        Components\Toggle::make('show_download_notes')
                                            ->label(__('pages.blocks.sermon_feed_show_notes'))
                                            ->default(false),
                                    ])->columns(2),

                                Components\Builder\Block::make('staff_directory')
                                    ->label(__('pages.blocks.staff_directory'))
                                    ->icon(Heroicon::OutlinedIdentification)
                                    ->schema([
                                        Components\TextInput::make('title')
                                            ->label(__('pages.blocks.staff_title'))
                                            ->default('Our Leadership')
                                            ->required(),

                                        Components\Select::make('department')
                                            ->label(__('pages.blocks.staff_department'))
                                            ->options([
                                                'all' => 'All Departments',
                                                'pastoral' => 'Pastoral Team',
                                                'admin' => 'Administration',
                                                'worship' => 'Worship & Arts',
                                            ])
                                            ->default('all'),
                                    ])->columns(2),

                                Components\Builder\Block::make('giving_cta')
                                    ->label(__('pages.blocks.giving_cta'))
                                    ->icon(Heroicon::OutlinedHeart)
                                    ->schema([
                                        Components\TextInput::make('title')
                                            ->label(__('pages.blocks.giving_title'))
                                            ->default('Support Our Mission')
                                            ->required(),

                                        Components\Textarea::make('description')
                                            ->label(__('pages.blocks.giving_desc'))
                                            ->default('Your generosity helps us reach more people with the message of hope.'),

                                        Components\TextInput::make('button_label')
                                            ->label(__('pages.blocks.giving_button'))
                                            ->default('Give Online Now'),

                                        Components\Select::make('fund_id')
                                            ->label(__('pages.blocks.giving_fund'))
                                            ->options(fn () => \App\Models\Fund::pluck('name', 'id'))
                                            ->placeholder('General Fund')
                                            ->searchable(),

                                        Components\Toggle::make('show_quick_give')
                                            ->label(__('pages.blocks.giving_show_quick_give'))
                                            ->default(false)
                                            ->live(),

                                        Components\TextInput::make('quick_give_amounts')
                                            ->label(__('pages.blocks.giving_quick_amounts'))
                                            ->default('10, 20, 50, 100')
                                            ->placeholder('e.g. 10, 20, 50, 100')
                                            ->visible(fn (callable $get) => $get('show_quick_give')),
                                    ])->columns(2),

                                Components\Builder\Block::make('events_feed')
                                    ->label(__('pages.blocks.events_feed'))
                                    ->icon(Heroicon::OutlinedCalendarDays)
                                    ->schema([
                                        Components\TextInput::make('title')
                                            ->label(__('pages.blocks.events_feed_title'))
                                            ->default('Upcoming Events')
                                            ->required(),

                                        Components\Select::make('limit')
                                            ->label(__('pages.blocks.events_feed_limit'))
                                            ->options([
                                                3 => '3 Events',
                                                6 => '6 Events',
                                                9 => '9 Events',
                                            ])
                                            ->default(3),

                                        Components\Select::make('campus_id')
                                            ->label(__('campuses.campus'))
                                            ->options(fn () => \App\Models\Campus::pluck('name', 'id'))
                                            ->placeholder('All Campuses')
                                            ->searchable(),

                                        Components\Toggle::make('show_past')
                                            ->label('Show Past Events')
                                            ->default(false),
                                    ])->columns(2),

                                Components\Builder\Block::make('live_stream')
                                    ->label(__('pages.blocks.live_stream'))
                                    ->icon(Heroicon::OutlinedPlayCircle)
                                    ->schema([
                                        Components\TextInput::make('title')
                                            ->label(__('pages.blocks.live_title'))
                                            ->default('Join us Live!')
                                            ->required(),

                                        Components\TextInput::make('stream_url')
                                            ->label('Stream URL')
                                            ->placeholder('YouTube/Facebook/Twitch link')
                                            ->url(),

                                        Components\Toggle::make('always_show')
                                            ->label('Always Show')
                                            ->default(true)
                                            ->helperText('Uncheck to only show when live (requires setting)'),
                                    ])->columns(2),

                                Components\Builder\Block::make('countdown_timer')
                                    ->label(__('pages.blocks.countdown_timer'))
                                    ->icon(Heroicon::OutlinedClock)
                                    ->schema([
                                        Components\TextInput::make('title')
                                            ->label(__('pages.blocks.countdown_title'))
                                            ->default('Big Event Starting In...')
                                            ->required(),

                                        Components\DateTimePicker::make('target_date')
                                            ->label('Target Date & Time')
                                            ->required(),

                                        Components\TextInput::make('cta_label')
                                            ->label('Button Label')
                                            ->maxLength(255),

                                        Components\TextInput::make('cta_url')
                                            ->label('Button URL')
                                            ->url(),
                                    ])->columns(2),

                                Components\Builder\Block::make('columns')
                                    ->label(__('pages.blocks.columns'))
                                    ->icon(Heroicon::OutlinedViewColumns)
                                    ->schema([
                                        Components\Select::make('layout')
                                            ->label(__('pages.blocks.columns_layout'))
                                            ->options([
                                                '50-50' => '50 / 50',
                                                '33-33-33' => '33 / 33 / 33',
                                                '70-30' => '70 / 30',
                                                '30-70' => '30 / 70',
                                            ])
                                            ->default('50-50')
                                            ->required(),

                                        Components\Repeater::make('columns')
                                            ->label(__('pages.blocks.columns_content'))
                                            ->schema([
                                                Components\Builder::make('content')
                                                    ->label(__('pages.blocks.content'))
                                                    ->blocks([
                                                        // Subset of blocks for nesting
                                                        Components\Builder\Block::make('heading')
                                                            ->label(__('pages.blocks.heading'))
                                                            ->schema([
                                                                Components\TextInput::make('content')->required(),
                                                                Components\Select::make('level')->options(['h2' => 'H2', 'h3' => 'H3', 'h4' => 'H4'])->default('h3'),
                                                            ]),
                                                        Components\Builder\Block::make('rich_text')
                                                            ->label(__('pages.blocks.rich_text'))
                                                            ->schema([
                                                                Components\MarkdownEditor::make('body')->required(),
                                                            ]),
                                                        Components\Builder\Block::make('image')
                                                            ->label(__('pages.blocks.image'))
                                                            ->schema([
                                                                Components\TextInput::make('url')->url()->required(),
                                                                Components\TextInput::make('alt'),
                                                            ]),
                                                        Components\Builder\Block::make('video')
                                                            ->label(__('pages.blocks.video'))
                                                            ->schema([
                                                                Components\TextInput::make('url')->url()->required(),
                                                            ]),
                                                        Components\Builder\Block::make('quote')
                                                            ->label(__('pages.blocks.quote'))
                                                            ->schema([
                                                                Components\Textarea::make('text')->required(),
                                                                Components\TextInput::make('attribution'),
                                                            ]),
                                                        Components\Builder\Block::make('call_to_action')
                                                            ->label(__('pages.blocks.call_to_action'))
                                                            ->schema([
                                                                Components\TextInput::make('label')->required(),
                                                                Components\TextInput::make('url')->url()->required(),
                                                            ]),
                                                    ]),
                                            ])
                                            ->minItems(2)
                                            ->maxItems(3)
                                            ->itemLabel(fn (array $state): ?string => 'Column '.($state['id'] ?? '')),
                                    ]),

                                Components\Builder\Block::make('tabs')
                                    ->label(__('pages.blocks.tabs'))
                                    ->icon(Heroicon::OutlinedSquare2Stack)
                                    ->schema([
                                        Components\Repeater::make('items')
                                            ->label(__('pages.blocks.tabs_items'))
                                            ->schema([
                                                Components\TextInput::make('title')->required(),
                                                Components\MarkdownEditor::make('content')->required(),
                                            ])
                                            ->itemLabel(fn (array $state): ?string => $state['title'] ?? null),
                                    ]),

                                Components\Builder\Block::make('spacer')
                                    ->label(__('pages.blocks.spacer'))
                                    ->icon(Heroicon::OutlinedArrowsUpDown)
                                    ->schema([
                                        Components\Select::make('size')
                                            ->label(__('pages.blocks.spacer_size'))
                                            ->options([
                                                'small' => 'Small (24px)',
                                                'medium' => 'Medium (48px)',
                                                'large' => 'Large (96px)',
                                                'xlarge' => 'X-Large (192px)',
                                            ])
                                            ->default('medium'),
                                    ]),

                                Components\Builder\Block::make('divider')
                                    ->label(__('pages.blocks.divider'))
                                    ->icon(Heroicon::OutlinedMinus)
                                    ->schema([
                                        Components\Select::make('style')
                                            ->label(__('pages.blocks.divider_style'))
                                            ->options([
                                                'solid' => 'Solid Line',
                                                'dashed' => 'Dashed Line',
                                                'gradient' => 'Gradient Fade',
                                                'waves' => __('pages.blocks.divider_waves'),
                                            ])
                                            ->default('solid'),
                                    ]),

                                Components\Builder\Block::make('gallery')
                                    ->label(__('pages.blocks.gallery'))
                                    ->icon(Heroicon::OutlinedPhoto)
                                    ->schema([
                                        Components\TextInput::make('title')
                                            ->label(__('pages.blocks.gallery_title'))
                                            ->maxLength(255),

                                        Components\Select::make('gallery_id')
                                            ->label(__('pages.blocks.gallery_source'))
                                            ->options(fn () => \App\Models\Gallery::pluck('title', 'id'))
                                            ->placeholder('Select a Gallery')
                                            ->searchable(),

                                        Components\Select::make('layout')
                                            ->label(__('pages.blocks.gallery_layout'))
                                            ->options([
                                                'grid' => 'Grid',
                                                'masonry' => 'Masonry',
                                                'mosaic' => __('pages.blocks.gallery_mosaic'),
                                                'slider' => 'Slider',
                                            ])
                                            ->default('grid'),
                                    ])->columns(2),

                                Components\Builder\Block::make('logo_cloud')
                                    ->label(__('pages.blocks.logo_cloud'))
                                    ->icon(Heroicon::OutlinedCloud)
                                    ->schema([
                                        Components\TextInput::make('title')
                                            ->label(__('pages.blocks.logo_cloud_title'))
                                            ->maxLength(255),

                                        Components\Repeater::make('logos')
                                            ->label(__('pages.blocks.logo_cloud_items'))
                                            ->schema([
                                                Components\TextInput::make('url')
                                                    ->label('Logo URL')
                                                    ->url()
                                                    ->required(),
                                                Components\TextInput::make('name')
                                                    ->label('Partner Name'),
                                                Components\TextInput::make('link')
                                                    ->label('Website Link')
                                                    ->url(),
                                            ])
                                            ->columns(3)
                                            ->grid(2),

                                        Group::make([
                                            Components\Toggle::make('is_carousel')
                                                ->label(__('pages.blocks.is_carousel'))
                                                ->default(true)
                                                ->live(),

                                            Components\TextInput::make('autoplay_speed')
                                                ->label(__('pages.blocks.autoplay_speed'))
                                                ->numeric()
                                                ->default(5000)
                                                ->visible(fn (callable $get) => $get('is_carousel')),
                                        ])->columns(2),
                                    ]),

                                Components\Builder\Block::make('newsletter_signup')
                                    ->label(__('pages.blocks.newsletter_signup'))
                                    ->icon(Heroicon::OutlinedAtSymbol)
                                    ->schema([
                                        Components\TextInput::make('title')
                                            ->label(__('pages.blocks.newsletter_title'))
                                            ->default('Stay Updated')
                                            ->required(),

                                        Components\Textarea::make('description')
                                            ->label(__('pages.blocks.newsletter_desc'))
                                            ->default('Subscribe to our newsletter to receive the latest news and updates.'),

                                        Components\TextInput::make('button_label')
                                            ->label(__('pages.blocks.newsletter_button'))
                                            ->default('Subscribe'),
                                    ]),

                                Components\Builder\Block::make('map')
                                    ->label(__('pages.blocks.map'))
                                    ->icon(Heroicon::OutlinedMap)
                                    ->schema([
                                        Components\TextInput::make('title')
                                            ->label(__('pages.blocks.map_title'))
                                            ->default('Our Location'),

                                        Components\Select::make('campus_id')
                                            ->label(__('pages.blocks.map_campus'))
                                            ->options(fn () => \App\Models\Campus::pluck('name', 'id'))
                                            ->placeholder('Select Campus for Address')
                                            ->searchable(),

                                        Components\TextInput::make('address')
                                            ->label('Manual Address')
                                            ->placeholder('E.g. 123 Church St, City, Country')
                                            ->helperText('Used if no campus is selected'),
                                    ])->columns(2),
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
