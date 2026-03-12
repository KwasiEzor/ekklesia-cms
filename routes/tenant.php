<?php

declare(strict_types=1);

use App\Livewire\Donation;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function (): void {
    Route::get('/', fn (): string => 'This is your multi-tenant application. The id of the current tenant is '.tenant('id'));

    Route::get('/give', Donation::class)->name('donation.give');
    Route::get('/donation/success', fn () => view('donation-success'))->name('donation.success');
});
