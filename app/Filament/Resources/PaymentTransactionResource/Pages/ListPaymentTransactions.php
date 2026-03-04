<?php

namespace App\Filament\Resources\PaymentTransactionResource\Pages;

use App\Filament\Resources\PaymentTransactionResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Width;

class ListPaymentTransactions extends ListRecords
{
    protected static string $resource = PaymentTransactionResource::class;

    protected Width|string|null $maxContentWidth = Width::Full;
}
