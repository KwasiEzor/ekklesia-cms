<?php

namespace App\Models;

use Laravel\Cashier\SubscriptionItem as CashierSubscriptionItem;

class SubscriptionItem extends CashierSubscriptionItem
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'subscription_id',
        'stripe_id',
        'stripe_product',
        'stripe_price',
        'quantity',
    ];
}
