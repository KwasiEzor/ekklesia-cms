<?php

use App\Filament\Pages\Dashboard;

test('dashboard page uses localized premium title', function () {
    $page = new Dashboard;

    expect($page->getTitle())->toBe(__('dashboard.control_center'));
});

test('dashboard page has localized premium subheading', function () {
    $page = new Dashboard;

    expect($page->getSubheading())->toBe(__('dashboard.control_center_subheading'));
});
