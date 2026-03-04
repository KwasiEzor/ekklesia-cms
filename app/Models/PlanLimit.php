<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlanLimit extends Model
{
    protected $fillable = [
        'plan_slug',
        'name',
        'max_members',
        'max_storage_mb',
        'max_campuses',
        'has_payments',
        'has_sms',
        'has_whatsapp',
        'has_ai',
        'ai_tier',
        'price_cents',
        'currency',
        'stripe_price_id',
    ];

    protected function casts(): array
    {
        return [
            'max_members' => 'integer',
            'max_storage_mb' => 'integer',
            'max_campuses' => 'integer',
            'has_payments' => 'boolean',
            'has_sms' => 'boolean',
            'has_whatsapp' => 'boolean',
            'has_ai' => 'boolean',
            'price_cents' => 'integer',
        ];
    }

    public function isUnlimited(string $field): bool
    {
        return ($this->getAttribute($field) ?? 0) === 0;
    }

    public function getFormattedPriceAttribute(): string
    {
        if ($this->price_cents === 0) {
            return __('billing.free');
        }

        return number_format($this->price_cents / 100, 2, '.', '').' '.$this->currency.'/mo';
    }
}
