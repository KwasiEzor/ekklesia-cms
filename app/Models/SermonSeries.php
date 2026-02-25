<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class SermonSeries extends Model
{
    use BelongsToTenant, HasFactory;

    protected $table = 'sermon_series';

    protected $fillable = [
        'title',
        'slug',
        'description',
        'tenant_id',
    ];

    public function sermons(): HasMany
    {
        return $this->hasMany(Sermon::class, 'series_id');
    }
}
