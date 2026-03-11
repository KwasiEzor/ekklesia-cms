<?php

namespace App\Models;

use App\Concerns\HasCampusScope;
use App\Concerns\HasSoftVersioning;
use App\Concerns\LogsActivityWithTenant;
use App\States\MemberStatus\MemberStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\ModelStates\HasStates;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class Member extends Model implements HasMedia
{
    use BelongsToTenant, HasCampusScope, HasFactory, HasSoftVersioning, HasStates, InteractsWithMedia, LogsActivityWithTenant;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'date_of_birth',
        'wedding_anniversary',
        'baptism_date',
        'cell_group_id',
        'campus_id',
        'household_id',
        'family_role',
        'status',
        'custom_fields',
        'tenant_id',
    ];

    protected $hidden = [
        'tenant_id',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'wedding_anniversary' => 'date',
            'baptism_date' => 'date',
            'status' => MemberStatus::class,
            'custom_fields' => 'array',
            'previous_version' => 'array',
        ];
    }

    public function cellGroup(): BelongsTo
    {
        return $this->belongsTo(CellGroup::class);
    }

    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class);
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function getNameAttribute(): string
    {
        return $this->full_name;
    }

    public function getAvatarUrlAttribute(): ?string
    {
        return $this->getFirstMediaUrl('avatar', 'thumb') ?: null;
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatar')
            ->singleFile()
            ->withResponsiveImages()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(150)
            ->height(150)
            ->sharpen(10)
            ->performOnCollections('avatar');

        $this->addMediaConversion('medium')
            ->width(400)
            ->height(400)
            ->sharpen(10)
            ->performOnCollections('avatar');
    }

    public function galleries(): MorphMany
    {
        return $this->morphMany(Gallery::class, 'galleryable');
    }

    public function scopeBirthdayToday(Builder $query): Builder
    {
        return $query->whereNotNull('date_of_birth')
            ->whereRaw('EXTRACT(MONTH FROM date_of_birth) = ? AND EXTRACT(DAY FROM date_of_birth) = ?', [
                now()->month,
                now()->day,
            ]);
    }

    public function scopeBirthdayThisWeek(Builder $query): Builder
    {
        $dates = collect(range(0, 6))->map(fn (int $i) => now()->addDays($i));

        return $query->whereNotNull('date_of_birth')
            ->where(function (Builder $q) use ($dates): void {
                foreach ($dates as $date) {
                    $q->orWhereRaw('EXTRACT(MONTH FROM date_of_birth) = ? AND EXTRACT(DAY FROM date_of_birth) = ?', [
                        $date->month,
                        $date->day,
                    ]);
                }
            });
    }

    public function scopeUpcomingBirthdays(Builder $query, int $days = 30): Builder
    {
        $dates = collect(range(0, $days - 1))->map(fn (int $i) => now()->addDays($i));

        return $query->whereNotNull('date_of_birth')
            ->where(function (Builder $q) use ($dates): void {
                foreach ($dates as $date) {
                    $q->orWhereRaw('EXTRACT(MONTH FROM date_of_birth) = ? AND EXTRACT(DAY FROM date_of_birth) = ?', [
                        $date->month,
                        $date->day,
                    ]);
                }
            });
    }

    public function scopeAnniversaryToday(Builder $query): Builder
    {
        return $query->whereNotNull('wedding_anniversary')
            ->whereRaw('EXTRACT(MONTH FROM wedding_anniversary) = ? AND EXTRACT(DAY FROM wedding_anniversary) = ?', [
                now()->month,
                now()->day,
            ]);
    }

    public function scopeAnniversaryThisWeek(Builder $query): Builder
    {
        $dates = collect(range(0, 6))->map(fn (int $i) => now()->addDays($i));

        return $query->whereNotNull('wedding_anniversary')
            ->where(function (Builder $q) use ($dates): void {
                foreach ($dates as $date) {
                    $q->orWhereRaw('EXTRACT(MONTH FROM wedding_anniversary) = ? AND EXTRACT(DAY FROM wedding_anniversary) = ?', [
                        $date->month,
                        $date->day,
                    ]);
                }
            });
    }

    public function getAgeAttribute(): ?int
    {
        return $this->date_of_birth?->age;
    }

    public function getYearsMarriedAttribute(): ?int
    {
        return $this->wedding_anniversary?->age;
    }

    public function isBirthdayToday(): bool
    {
        return $this->date_of_birth !== null
            && $this->date_of_birth->month === now()->month
            && $this->date_of_birth->day === now()->day;
    }

    public function isAnniversaryToday(): bool
    {
        return $this->wedding_anniversary !== null
            && $this->wedding_anniversary->month === now()->month
            && $this->wedding_anniversary->day === now()->day;
    }
}
