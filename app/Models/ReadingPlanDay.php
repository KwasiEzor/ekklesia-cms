<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class ReadingPlanDay extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'reading_plan_id',
        'day_number',
        'title',
        'passage_reference',
        'passage_text',
        'reflection',
        'tenant_id',
    ];

    protected $hidden = [
        'tenant_id',
    ];

    protected function casts(): array
    {
        return [
            'day_number' => 'integer',
        ];
    }

    public function readingPlan(): BelongsTo
    {
        return $this->belongsTo(ReadingPlan::class);
    }
}
