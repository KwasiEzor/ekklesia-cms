<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function (): void {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('members:send-birthday-notifications')->dailyAt('07:00');
Schedule::command('bulk-messages:send-scheduled')->everyMinute();
