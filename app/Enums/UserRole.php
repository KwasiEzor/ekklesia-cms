<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasDescription;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum UserRole: string implements HasColor, HasDescription, HasIcon, HasLabel
{
    case SUPER_ADMIN = 'super_admin';
    case ADMIN = 'admin';
    case PASTOR = 'pastor';
    case PROPHET = 'prophet';
    case TREASURER = 'treasurer';
    case VOLUNTEER = 'volunteer';
    case MEMBER = 'member';

    public function getLabel(): string
    {
        return match ($this) {
            self::SUPER_ADMIN => 'Super Admin',
            self::ADMIN => 'Admin',
            self::PASTOR => 'Pastor',
            self::PROPHET => 'Prophet',
            self::TREASURER => 'Treasurer',
            self::VOLUNTEER => 'Volunteer',
            self::MEMBER => 'Member',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::SUPER_ADMIN => 'danger',
            self::ADMIN => 'warning',
            self::PASTOR => 'primary',
            self::PROPHET => 'info',
            self::TREASURER => 'success',
            self::VOLUNTEER => 'gray',
            self::MEMBER => 'info',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::SUPER_ADMIN => 'heroicon-m-shield-check',
            self::ADMIN => 'heroicon-m-user-group',
            self::PASTOR => 'heroicon-m-microphone',
            self::PROPHET => 'heroicon-m-sparkles',
            self::TREASURER => 'heroicon-m-banknotes',
            self::VOLUNTEER => 'heroicon-m-hand-raised',
            self::MEMBER => 'heroicon-m-user',
        };
    }

    public function getDescription(): ?string
    {
        return match ($this) {
            self::SUPER_ADMIN => 'Global administrator with full system access across all tenants.',
            self::ADMIN => 'Tenant administrator with full control over their own church data.',
            self::PASTOR => 'Focuses on ministry content like sermons, events, and announcements.',
            self::PROPHET => 'Spiritual leader focused on prophecies, events, and community guidance.',
            self::TREASURER => 'Manages financial records, giving, and payment transactions.',
            self::VOLUNTEER => 'Limited access for basic data entry and content viewing.',
            self::MEMBER => 'Standard church member with access to personal dashboard and public content.',
        };
    }

    /**
     * Determine if this role is typically global (team_id is null).
     */
    public function isGlobal(): bool
    {
        return match ($this) {
            self::SUPER_ADMIN => true,
            default => false,
        };
    }

    /**
     * Determine if the role has administrative privileges.
     */
    public function isAdministrative(): bool
    {
        return in_array($this, [self::SUPER_ADMIN, self::ADMIN]);
    }

    /**
     * Determine if the role is a ministry/spiritual role.
     */
    public function isMinistry(): bool
    {
        return in_array($this, [self::PASTOR, self::PROPHET]);
    }

    /**
     * Roles that are allowed to access the Filament Admin panel.
     */
    public function canAccessPanel(): bool
    {
        return $this !== self::MEMBER;
    }

    /**
     * Get the default permissions for this role.
     */
    public function getPermissions(): array
    {
        return match ($this) {
            self::SUPER_ADMIN => [], // Bypasses all checks
            self::ADMIN => [
                'ViewAny:Sermon', 'View:Sermon', 'Create:Sermon', 'Update:Sermon', 'Delete:Sermon',
                'ViewAny:Event', 'View:Event', 'Create:Event', 'Update:Event', 'Delete:Event',
                'ViewAny:Announcement', 'View:Announcement', 'Create:Announcement', 'Update:Announcement', 'Delete:Announcement',
                'ViewAny:Page', 'View:Page', 'Create:Page', 'Update:Page', 'Delete:Page',
                'ViewAny:Gallery', 'View:Gallery', 'Create:Gallery', 'Update:Gallery', 'Delete:Gallery',
                'ViewAny:Campus', 'View:Campus', 'Create:Campus', 'Update:Campus', 'Delete:Campus',
                'ViewAny:Member', 'View:Member', 'Create:Member', 'Update:Member', 'Delete:Member',
                'ViewAny:GivingRecord', 'View:GivingRecord', 'Create:GivingRecord', 'Update:GivingRecord',
                'ViewAny:PaymentTransaction', 'View:PaymentTransaction',
                'ViewAny:User', 'View:User', 'Create:User', 'Update:User', 'Delete:User',
                'ViewAny:Attendance', 'View:Attendance', 'Create:Attendance', 'Update:Attendance', 'Delete:Attendance',
                'ViewAny:ServiceType', 'View:ServiceType', 'Create:ServiceType', 'Update:ServiceType', 'Delete:ServiceType',
                'ViewAny:Household', 'View:Household', 'Create:Household', 'Update:Household', 'Delete:Household',
                'ViewAny:PrayerRequest', 'View:PrayerRequest', 'Create:PrayerRequest', 'Update:PrayerRequest', 'Delete:PrayerRequest',
                'ViewAny:Fund', 'View:Fund', 'Create:Fund', 'Update:Fund', 'Delete:Fund',
                'ViewAny:Campaign', 'View:Campaign', 'Create:Campaign', 'Update:Campaign', 'Delete:Campaign',
                'ViewAny:Devotional', 'View:Devotional', 'Create:Devotional', 'Update:Devotional', 'Delete:Devotional',
                'ViewAny:Testimony', 'View:Testimony', 'Create:Testimony', 'Update:Testimony', 'Delete:Testimony',
                'ViewAny:ReadingPlan', 'View:ReadingPlan', 'Create:ReadingPlan', 'Update:ReadingPlan', 'Delete:ReadingPlan',
                'ViewAny:BulkMessage', 'View:BulkMessage', 'Create:BulkMessage', 'Update:BulkMessage', 'Delete:BulkMessage',
            ],
            self::PASTOR, self::PROPHET => [
                'ViewAny:Sermon', 'View:Sermon', 'Create:Sermon', 'Update:Sermon', 'Delete:Sermon',
                'ViewAny:Event', 'View:Event', 'Create:Event', 'Update:Event', 'Delete:Event',
                'ViewAny:Announcement', 'View:Announcement', 'Create:Announcement', 'Update:Announcement', 'Delete:Announcement',
                'ViewAny:Page', 'View:Page', 'Create:Page', 'Update:Page', 'Delete:Page',
                'ViewAny:Gallery', 'View:Gallery', 'Create:Gallery', 'Update:Gallery', 'Delete:Gallery',
                'ViewAny:Campus', 'View:Campus', 'Create:Campus', 'Update:Campus', 'Delete:Campus',
                'ViewAny:Member', 'View:Member', 'ViewAny:User',
                'ViewAny:Attendance', 'View:Attendance', 'Create:Attendance', 'Update:Attendance',
                'ViewAny:ServiceType', 'View:ServiceType', 'Create:ServiceType', 'Update:ServiceType',
                'ViewAny:Household', 'View:Household', 'Create:Household', 'Update:Household',
                'ViewAny:PrayerRequest', 'View:PrayerRequest', 'Create:PrayerRequest', 'Update:PrayerRequest',
                'ViewAny:Fund', 'View:Fund',
                'ViewAny:Campaign', 'View:Campaign',
                'ViewAny:Devotional', 'View:Devotional', 'Create:Devotional', 'Update:Devotional', 'Delete:Devotional',
                'ViewAny:Testimony', 'View:Testimony', 'Create:Testimony', 'Update:Testimony', 'Delete:Testimony',
                'ViewAny:ReadingPlan', 'View:ReadingPlan', 'Create:ReadingPlan', 'Update:ReadingPlan', 'Delete:ReadingPlan',
                'ViewAny:BulkMessage', 'View:BulkMessage', 'Create:BulkMessage', 'Update:BulkMessage',
            ],
            self::TREASURER => [
                'ViewAny:GivingRecord', 'View:GivingRecord', 'Create:GivingRecord',
                'ViewAny:PaymentTransaction', 'View:PaymentTransaction',
                'ViewAny:Member', 'View:Member', 'ViewAny:User',
                'ViewAny:Fund', 'View:Fund', 'Create:Fund', 'Update:Fund',
                'ViewAny:Campaign', 'View:Campaign', 'Create:Campaign', 'Update:Campaign',
            ],
            self::VOLUNTEER => [
                'ViewAny:Sermon', 'View:Sermon',
                'ViewAny:Event', 'View:Event',
                'ViewAny:Announcement', 'View:Announcement',
                'ViewAny:User',
                'ViewAny:Attendance', 'View:Attendance', 'Create:Attendance',
            ],
            self::MEMBER => [
                'ViewAny:Sermon', 'View:Sermon',
                'ViewAny:Event', 'View:Event',
                'ViewAny:Announcement', 'View:Announcement',
            ],
        };
    }

    /**
     * Get values as a simple array.
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get options for a select component.
     */
    public static function asOptions(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $role): array => [
            $role->value => $role->getLabel(),
        ])->toArray();
    }
}
