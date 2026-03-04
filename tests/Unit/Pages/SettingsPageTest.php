<?php

use App\Filament\Pages\Settings;
use Filament\Support\Enums\Width;

test('settings page uses full content width for complex configuration forms', function () {
    $page = new Settings;

    expect($page->getMaxContentWidth())->toBe(Width::Full);
});
