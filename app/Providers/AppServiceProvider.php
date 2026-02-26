<?php

namespace App\Providers;

use App\Events\ContentChanged;
use App\Listeners\NotifyTenantAdmins;
use App\Models\Announcement;
use App\Models\Event;
use App\Models\Gallery;
use App\Models\Member;
use App\Models\Sermon;
use App\Observers\ContentObserver;
use Illuminate\Support\Facades\Event as EventFacade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Sermon::observe(ContentObserver::class);
        Event::observe(ContentObserver::class);
        Announcement::observe(ContentObserver::class);
        Member::observe(ContentObserver::class);
        Gallery::observe(ContentObserver::class);

        EventFacade::listen(ContentChanged::class, NotifyTenantAdmins::class);
    }
}
