<?php

namespace App\Filament\Pages;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Facades\Filament;
use Filament\Forms\Components;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class Settings extends Page
{
    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?int $navigationSort = 99;

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    public static function getNavigationLabel(): string
    {
        return __('settings.title');
    }

    public function getTitle(): string
    {
        return __('settings.title');
    }

    public function getSubheading(): ?string
    {
        return __('settings.description');
    }

    public function mount(): void
    {
        $tenant = Filament::getTenant();

        $this->form->fill([
            'name' => $tenant->name,
            'data' => $tenant->data ?? [],
        ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getFormContentComponent(),
            ]);
    }

    public function getFormContentComponent(): Component
    {
        return Form::make([EmbeddedSchema::make('form')])
            ->id('form')
            ->livewireSubmitHandler('save')
            ->footer([
                Actions::make($this->getFormActions())
                    ->sticky()
                    ->key('form-actions'),
            ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Tabs::make(__('settings.title'))
                    ->tabs([
                        $this->churchInfoTab(),
                        $this->appearanceTab(),
                        $this->socialTab(),
                        $this->seoTab(),
                        $this->notificationsTab(),
                        $this->modulesTab(),
                        $this->advancedTab(),
                    ])
                    ->columnSpanFull()
                    ->persistTabInQueryString(),
            ]);
    }

    /**
     * @return array<Action | ActionGroup>
     */
    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label(__('settings.save'))
                ->submit('save')
                ->keyBindings(['mod+s']),
        ];
    }

    public function save(): void
    {
        $state = $this->form->getState();

        $tenant = Filament::getTenant();
        $tenant->name = $state['name'];
        $tenant->data = $state['data'] ?? [];
        $tenant->save();

        Notification::make()
            ->title(__('settings.saved'))
            ->success()
            ->send();
    }

    protected function churchInfoTab(): Tab
    {
        return Tab::make(__('settings.tab_church_info'))
            ->icon(Heroicon::OutlinedBuildingLibrary)
            ->schema([
                Section::make(__('settings.section_identity'))
                    ->description(__('settings.section_identity_desc'))
                    ->icon(Heroicon::OutlinedBuildingLibrary)
                    ->schema([
                        Components\TextInput::make('name')
                            ->label(__('settings.church_name'))
                            ->placeholder(__('settings.church_name_placeholder'))
                            ->required()
                            ->maxLength(255),

                        Components\TextInput::make('data.pastor_name')
                            ->label(__('settings.pastor_name'))
                            ->placeholder(__('settings.pastor_name_placeholder'))
                            ->prefixIcon(Heroicon::OutlinedUser)
                            ->maxLength(255),

                        Components\TextInput::make('data.denomination')
                            ->label(__('settings.denomination'))
                            ->placeholder(__('settings.denomination_placeholder'))
                            ->maxLength(255),

                        Components\TextInput::make('data.year_founded')
                            ->label(__('settings.year_founded'))
                            ->placeholder(__('settings.year_founded_placeholder'))
                            ->numeric()
                            ->minValue(1800)
                            ->maxValue(date('Y')),

                        Components\TextInput::make('data.church_capacity')
                            ->label(__('settings.church_capacity'))
                            ->placeholder(__('settings.church_capacity_placeholder'))
                            ->numeric()
                            ->minValue(0),

                        Components\Textarea::make('data.description')
                            ->label(__('settings.church_description'))
                            ->placeholder(__('settings.church_description_placeholder'))
                            ->rows(3)
                            ->maxLength(500)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make(__('settings.section_contact'))
                    ->description(__('settings.section_contact_desc'))
                    ->icon(Heroicon::OutlinedPhone)
                    ->schema([
                        Components\Textarea::make('data.address')
                            ->label(__('settings.address'))
                            ->placeholder(__('settings.address_placeholder'))
                            ->rows(2)
                            ->maxLength(500)
                            ->columnSpanFull(),

                        Components\TextInput::make('data.phone')
                            ->label(__('settings.phone'))
                            ->placeholder(__('settings.phone_placeholder'))
                            ->prefixIcon(Heroicon::OutlinedPhone)
                            ->tel()
                            ->maxLength(255),

                        Components\TextInput::make('data.phone_secondary')
                            ->label(__('settings.phone_secondary'))
                            ->placeholder(__('settings.phone_secondary_placeholder'))
                            ->prefixIcon(Heroicon::OutlinedPhone)
                            ->tel()
                            ->maxLength(255),

                        Components\TextInput::make('data.email')
                            ->label(__('settings.email'))
                            ->placeholder(__('settings.email_placeholder'))
                            ->prefixIcon(Heroicon::OutlinedEnvelope)
                            ->email()
                            ->maxLength(255),

                        Components\TextInput::make('data.website')
                            ->label(__('settings.website'))
                            ->placeholder(__('settings.website_placeholder'))
                            ->prefixIcon(Heroicon::OutlinedGlobeAlt)
                            ->url()
                            ->maxLength(2048),
                    ])
                    ->columns(2),

                Section::make(__('settings.section_worship'))
                    ->description(__('settings.section_worship_desc'))
                    ->icon(Heroicon::OutlinedClock)
                    ->collapsible()
                    ->schema([
                        Components\Repeater::make('data.worship_schedule')
                            ->label(__('settings.worship_schedule'))
                            ->schema([
                                Components\TextInput::make('label')
                                    ->label(__('settings.worship_label'))
                                    ->placeholder(__('settings.worship_label_placeholder'))
                                    ->maxLength(255),

                                Components\Select::make('day')
                                    ->label(__('settings.worship_day'))
                                    ->options([
                                        'monday' => __('settings.days.monday'),
                                        'tuesday' => __('settings.days.tuesday'),
                                        'wednesday' => __('settings.days.wednesday'),
                                        'thursday' => __('settings.days.thursday'),
                                        'friday' => __('settings.days.friday'),
                                        'saturday' => __('settings.days.saturday'),
                                        'sunday' => __('settings.days.sunday'),
                                    ])
                                    ->required(),

                                Components\TimePicker::make('time')
                                    ->label(__('settings.worship_time'))
                                    ->required(),
                            ])
                            ->columns(3)
                            ->defaultItems(1)
                            ->reorderable()
                            ->collapsible()
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    protected function appearanceTab(): Tab
    {
        return Tab::make(__('settings.tab_appearance'))
            ->icon(Heroicon::OutlinedPaintBrush)
            ->schema([
                Section::make(__('settings.section_branding'))
                    ->description(__('settings.section_branding_desc'))
                    ->icon(Heroicon::OutlinedPhoto)
                    ->schema([
                        Components\FileUpload::make('data.logo')
                            ->label(__('settings.logo'))
                            ->helperText(__('settings.logo_help'))
                            ->image()
                            ->directory('tenants/logos')
                            ->maxSize(2048)
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/svg+xml', 'image/webp']),

                        Components\FileUpload::make('data.favicon')
                            ->label(__('settings.favicon'))
                            ->helperText(__('settings.favicon_help'))
                            ->image()
                            ->directory('tenants/favicons')
                            ->maxSize(512)
                            ->acceptedFileTypes(['image/png', 'image/x-icon', 'image/vnd.microsoft.icon']),

                        Components\FileUpload::make('data.cover_image')
                            ->label(__('settings.cover_image'))
                            ->helperText(__('settings.cover_image_help'))
                            ->image()
                            ->directory('tenants/covers')
                            ->maxSize(5120)
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make(__('settings.section_colors'))
                    ->description(__('settings.section_colors_desc'))
                    ->icon(Heroicon::OutlinedSwatch)
                    ->schema([
                        Components\ColorPicker::make('data.primary_color')
                            ->label(__('settings.primary_color'))
                            ->helperText(__('settings.primary_color_help')),

                        Components\ColorPicker::make('data.secondary_color')
                            ->label(__('settings.secondary_color'))
                            ->helperText(__('settings.secondary_color_help')),
                    ])
                    ->columns(2),

                Section::make(__('settings.section_typography'))
                    ->description(__('settings.section_typography_desc'))
                    ->icon(Heroicon::OutlinedLanguage)
                    ->collapsible()
                    ->schema([
                        Components\Select::make('data.heading_font')
                            ->label(__('settings.heading_font'))
                            ->options([
                                'Inter' => 'Inter',
                                'Poppins' => 'Poppins',
                                'Playfair Display' => 'Playfair Display',
                                'Montserrat' => 'Montserrat',
                                'Raleway' => 'Raleway',
                                'Lora' => 'Lora',
                            ]),

                        Components\Select::make('data.body_font')
                            ->label(__('settings.body_font'))
                            ->options([
                                'Inter' => 'Inter',
                                'Open Sans' => 'Open Sans',
                                'Roboto' => 'Roboto',
                                'Nunito' => 'Nunito',
                                'Source Sans' => 'Source Sans',
                                'Lato' => 'Lato',
                            ]),

                        Components\Select::make('data.base_font_size')
                            ->label(__('settings.base_font_size'))
                            ->options([
                                '14px' => '14px',
                                '15px' => '15px',
                                '16px' => '16px',
                                '18px' => '18px',
                            ])
                            ->default('16px'),

                        Components\Toggle::make('data.dark_mode')
                            ->label(__('settings.dark_mode'))
                            ->helperText(__('settings.dark_mode_help')),
                    ])
                    ->columns(2),

                Section::make(__('settings.custom_css'))
                    ->icon(Heroicon::OutlinedCodeBracket)
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Components\Textarea::make('data.custom_css')
                            ->label(__('settings.custom_css'))
                            ->helperText(__('settings.custom_css_help'))
                            ->rows(6)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    protected function socialTab(): Tab
    {
        return Tab::make(__('settings.tab_social'))
            ->icon(Heroicon::OutlinedShare)
            ->schema([
                Section::make(__('settings.tab_social'))
                    ->description(__('settings.social_desc'))
                    ->icon(Heroicon::OutlinedShare)
                    ->schema([
                        Components\TextInput::make('data.social_facebook')
                            ->label(__('settings.social_facebook'))
                            ->placeholder(__('settings.social_facebook_placeholder'))
                            ->url()
                            ->maxLength(2048),

                        Components\TextInput::make('data.social_instagram')
                            ->label(__('settings.social_instagram'))
                            ->placeholder(__('settings.social_instagram_placeholder'))
                            ->url()
                            ->maxLength(2048),

                        Components\TextInput::make('data.social_youtube')
                            ->label(__('settings.social_youtube'))
                            ->placeholder(__('settings.social_youtube_placeholder'))
                            ->url()
                            ->maxLength(2048),

                        Components\TextInput::make('data.social_twitter')
                            ->label(__('settings.social_twitter'))
                            ->placeholder(__('settings.social_twitter_placeholder'))
                            ->url()
                            ->maxLength(2048),

                        Components\TextInput::make('data.social_tiktok')
                            ->label(__('settings.social_tiktok'))
                            ->placeholder(__('settings.social_tiktok_placeholder'))
                            ->url()
                            ->maxLength(2048),

                        Components\TextInput::make('data.social_whatsapp')
                            ->label(__('settings.social_whatsapp'))
                            ->placeholder(__('settings.social_whatsapp_placeholder'))
                            ->url()
                            ->maxLength(2048),
                    ])
                    ->columns(2),
            ]);
    }

    protected function seoTab(): Tab
    {
        return Tab::make(__('settings.tab_seo'))
            ->icon(Heroicon::OutlinedMagnifyingGlass)
            ->schema([
                Section::make(__('settings.section_seo'))
                    ->description(__('settings.section_seo_desc'))
                    ->icon(Heroicon::OutlinedMagnifyingGlass)
                    ->schema([
                        Components\TextInput::make('data.seo_title_suffix')
                            ->label(__('settings.seo_title_suffix'))
                            ->placeholder(__('settings.seo_title_suffix_placeholder'))
                            ->helperText(__('settings.seo_title_suffix_help'))
                            ->maxLength(100),

                        Components\TextInput::make('data.seo_keywords')
                            ->label(__('settings.seo_keywords'))
                            ->placeholder(__('settings.seo_keywords_placeholder'))
                            ->helperText(__('settings.seo_keywords_help'))
                            ->maxLength(500),

                        Components\Textarea::make('data.seo_meta_description')
                            ->label(__('settings.seo_meta_description'))
                            ->placeholder(__('settings.seo_meta_description_placeholder'))
                            ->helperText(__('settings.seo_meta_description_help'))
                            ->rows(3)
                            ->maxLength(160)
                            ->columnSpanFull(),

                        Components\FileUpload::make('data.seo_og_image')
                            ->label(__('settings.seo_og_image'))
                            ->helperText(__('settings.seo_og_image_help'))
                            ->image()
                            ->directory('tenants/og')
                            ->maxSize(2048)
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make(__('settings.section_analytics'))
                    ->description(__('settings.section_analytics_desc'))
                    ->icon(Heroicon::OutlinedChartBar)
                    ->collapsible()
                    ->schema([
                        Components\TextInput::make('data.google_analytics_id')
                            ->label(__('settings.google_analytics_id'))
                            ->placeholder(__('settings.google_analytics_id_placeholder'))
                            ->helperText(__('settings.google_analytics_id_help'))
                            ->maxLength(20),

                        Components\TextInput::make('data.google_tag_manager_id')
                            ->label(__('settings.google_tag_manager_id'))
                            ->placeholder(__('settings.google_tag_manager_id_placeholder'))
                            ->helperText(__('settings.google_tag_manager_id_help'))
                            ->maxLength(20),

                        Components\TextInput::make('data.facebook_pixel_id')
                            ->label(__('settings.facebook_pixel_id'))
                            ->placeholder(__('settings.facebook_pixel_id_placeholder'))
                            ->helperText(__('settings.facebook_pixel_id_help'))
                            ->maxLength(20),
                    ])
                    ->columns(2),
            ]);
    }

    protected function notificationsTab(): Tab
    {
        return Tab::make(__('settings.tab_notifications'))
            ->icon(Heroicon::OutlinedBellAlert)
            ->schema([
                Section::make(__('settings.section_email'))
                    ->description(__('settings.section_email_desc'))
                    ->icon(Heroicon::OutlinedEnvelope)
                    ->schema([
                        Components\TextInput::make('data.email_sender_name')
                            ->label(__('settings.email_sender_name'))
                            ->placeholder(__('settings.email_sender_name_placeholder'))
                            ->helperText(__('settings.email_sender_name_help'))
                            ->maxLength(255),

                        Components\TextInput::make('data.email_reply_to')
                            ->label(__('settings.email_reply_to'))
                            ->placeholder(__('settings.email_reply_to_placeholder'))
                            ->helperText(__('settings.email_reply_to_help'))
                            ->email()
                            ->maxLength(255),

                        Components\TextInput::make('data.email_footer_text')
                            ->label(__('settings.email_footer_text'))
                            ->placeholder(__('settings.email_footer_text_placeholder'))
                            ->helperText(__('settings.email_footer_text_help'))
                            ->maxLength(500)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make(__('settings.section_notification_prefs'))
                    ->description(__('settings.section_notification_prefs_desc'))
                    ->icon(Heroicon::OutlinedBell)
                    ->schema([
                        Components\Toggle::make('data.notify_welcome_member')
                            ->label(__('settings.notify_welcome_member'))
                            ->helperText(__('settings.notify_welcome_member_help'))
                            ->default(true),

                        Components\Toggle::make('data.notify_giving_receipt')
                            ->label(__('settings.notify_giving_receipt'))
                            ->helperText(__('settings.notify_giving_receipt_help'))
                            ->default(true),

                        Components\Toggle::make('data.notify_event_reminder')
                            ->label(__('settings.notify_event_reminder'))
                            ->helperText(__('settings.notify_event_reminder_help'))
                            ->default(true),

                        Components\Select::make('data.notify_event_reminder_hours')
                            ->label(__('settings.notify_event_reminder_hours'))
                            ->helperText(__('settings.notify_event_reminder_hours_help'))
                            ->options([
                                '1' => '1h',
                                '2' => '2h',
                                '6' => '6h',
                                '12' => '12h',
                                '24' => '24h',
                                '48' => '48h',
                            ])
                            ->default('24'),

                        Components\Toggle::make('data.notify_new_announcement')
                            ->label(__('settings.notify_new_announcement'))
                            ->helperText(__('settings.notify_new_announcement_help'))
                            ->default(false),
                    ])
                    ->columns(2),

                Section::make(__('settings.section_welcome_message'))
                    ->description(__('settings.section_welcome_message_desc'))
                    ->icon(Heroicon::OutlinedChatBubbleBottomCenterText)
                    ->collapsible()
                    ->schema([
                        Components\TextInput::make('data.welcome_message_subject')
                            ->label(__('settings.welcome_message_subject'))
                            ->placeholder(__('settings.welcome_message_subject_placeholder'))
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Components\Textarea::make('data.welcome_message_body')
                            ->label(__('settings.welcome_message_body'))
                            ->placeholder(__('settings.welcome_message_body_placeholder'))
                            ->helperText(__('settings.welcome_message_body_help'))
                            ->rows(5)
                            ->maxLength(2000)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    protected function modulesTab(): Tab
    {
        return Tab::make(__('settings.tab_modules'))
            ->icon(Heroicon::OutlinedSquares2x2)
            ->schema([
                Section::make(__('settings.section_modules'))
                    ->description(__('settings.section_modules_desc'))
                    ->icon(Heroicon::OutlinedSquares2x2)
                    ->schema([
                        Components\Toggle::make('data.enable_sermons')
                            ->label(__('settings.enable_sermons'))
                            ->helperText(__('settings.enable_sermons_help'))
                            ->default(true),

                        Components\Toggle::make('data.enable_events')
                            ->label(__('settings.enable_events'))
                            ->helperText(__('settings.enable_events_help'))
                            ->default(true),

                        Components\Toggle::make('data.enable_announcements')
                            ->label(__('settings.enable_announcements'))
                            ->helperText(__('settings.enable_announcements_help'))
                            ->default(true),

                        Components\Toggle::make('data.enable_members')
                            ->label(__('settings.enable_members'))
                            ->helperText(__('settings.enable_members_help'))
                            ->default(true),

                        Components\Toggle::make('data.enable_pages')
                            ->label(__('settings.enable_pages'))
                            ->helperText(__('settings.enable_pages_help'))
                            ->default(true),

                        Components\Toggle::make('data.enable_giving')
                            ->label(__('settings.enable_giving'))
                            ->helperText(__('settings.enable_giving_help'))
                            ->default(true),

                        Components\Toggle::make('data.enable_galleries')
                            ->label(__('settings.enable_galleries'))
                            ->helperText(__('settings.enable_galleries_help'))
                            ->default(true),
                    ])
                    ->columns(2),
            ]);
    }

    protected function advancedTab(): Tab
    {
        return Tab::make(__('settings.tab_advanced'))
            ->icon(Heroicon::OutlinedCog6Tooth)
            ->schema([
                Section::make(__('settings.section_locale'))
                    ->description(__('settings.section_locale_desc'))
                    ->icon(Heroicon::OutlinedGlobeAlt)
                    ->schema([
                        Components\Select::make('data.default_locale')
                            ->label(__('settings.default_locale'))
                            ->options([
                                'fr' => __('settings.locales.fr'),
                                'en' => __('settings.locales.en'),
                            ])
                            ->default('fr'),

                        Components\Select::make('data.timezone')
                            ->label(__('settings.timezone'))
                            ->options(
                                collect(timezone_identifiers_list())
                                    ->mapWithKeys(fn (string $tz) => [$tz => $tz])
                                    ->toArray()
                            )
                            ->searchable()
                            ->default('Africa/Lome'),

                        Components\Select::make('data.default_currency')
                            ->label(__('settings.default_currency'))
                            ->options([
                                'XOF' => __('settings.currencies.XOF'),
                                'XAF' => __('settings.currencies.XAF'),
                                'EUR' => __('settings.currencies.EUR'),
                                'USD' => __('settings.currencies.USD'),
                                'GBP' => __('settings.currencies.GBP'),
                                'CAD' => __('settings.currencies.CAD'),
                            ])
                            ->default('XOF'),

                        Components\Select::make('data.date_format')
                            ->label(__('settings.date_format'))
                            ->helperText(__('settings.date_format_help'))
                            ->options([
                                'd/m/Y' => __('settings.date_formats.d/m/Y'),
                                'm/d/Y' => __('settings.date_formats.m/d/Y'),
                                'd M Y' => __('settings.date_formats.d M Y'),
                                'd F Y' => __('settings.date_formats.d F Y'),
                            ])
                            ->default('d/m/Y'),

                        Components\Select::make('data.time_format')
                            ->label(__('settings.time_format'))
                            ->helperText(__('settings.time_format_help'))
                            ->options([
                                'H:i' => __('settings.time_formats.H:i'),
                                'h:i A' => __('settings.time_formats.h:i A'),
                            ])
                            ->default('H:i'),
                    ])
                    ->columns(2),

                Section::make(__('settings.section_display'))
                    ->description(__('settings.section_display_desc'))
                    ->icon(Heroicon::OutlinedAdjustmentsHorizontal)
                    ->schema([
                        Components\Select::make('data.per_page')
                            ->label(__('settings.per_page'))
                            ->helperText(__('settings.per_page_help'))
                            ->options([
                                '10' => '10',
                                '15' => '15',
                                '25' => '25',
                                '50' => '50',
                            ])
                            ->default('15'),

                        Components\Toggle::make('data.public_registration')
                            ->label(__('settings.public_registration'))
                            ->helperText(__('settings.public_registration_help')),
                    ])
                    ->columns(2),

                Section::make(__('settings.section_api'))
                    ->description(__('settings.section_api_desc'))
                    ->icon(Heroicon::OutlinedCodeBracket)
                    ->collapsible()
                    ->schema([
                        Components\Toggle::make('data.api_enabled')
                            ->label(__('settings.api_enabled'))
                            ->helperText(__('settings.api_enabled_help'))
                            ->default(true),

                        Components\Select::make('data.api_rate_limit')
                            ->label(__('settings.api_rate_limit'))
                            ->helperText(__('settings.api_rate_limit_help'))
                            ->options([
                                '30' => '30 / min',
                                '60' => '60 / min',
                                '120' => '120 / min',
                                '300' => '300 / min',
                            ])
                            ->default('60'),
                    ])
                    ->columns(2),

                Section::make(__('settings.section_maintenance'))
                    ->description(__('settings.section_maintenance_desc'))
                    ->icon(Heroicon::OutlinedWrench)
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Components\Toggle::make('data.maintenance_mode')
                            ->label(__('settings.maintenance_mode'))
                            ->helperText(__('settings.maintenance_mode_help')),

                        Components\Textarea::make('data.maintenance_message')
                            ->label(__('settings.maintenance_message'))
                            ->placeholder(__('settings.maintenance_message_placeholder'))
                            ->helperText(__('settings.maintenance_message_help'))
                            ->rows(3)
                            ->maxLength(500)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
