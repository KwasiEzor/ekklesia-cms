<?php

namespace App\Providers;

use App\Events\ContentChanged;
use App\Listeners\NotifyTenantAdmins;
use App\Models\Announcement;
use App\Models\Campus;
use App\Models\Event;
use App\Models\Gallery;
use App\Models\GivingRecord;
use App\Models\Member;
use App\Models\Page;
use App\Models\Sermon;
use App\Observers\ContentObserver;
use App\Services\Ai\AiManager;
use App\Services\Ai\SkillRegistry;
use App\Services\Billing\PlanLimitsEnforcer;
use App\Services\Notification\NotificationChannelManager;
use App\Services\Payment\PaymentManager;
use Illuminate\Support\Facades\Event as EventFacade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SkillRegistry::class);
        $this->app->singleton(AiManager::class, fn ($app): \App\Services\Ai\AiManager => new AiManager($app));
        $this->app->alias(AiManager::class, 'ai');
        $this->app->singleton(PaymentManager::class, fn ($app): \App\Services\Payment\PaymentManager => new PaymentManager($app));
        $this->app->alias(PaymentManager::class, 'payments');
        $this->app->singleton(NotificationChannelManager::class, fn ($app): \App\Services\Notification\NotificationChannelManager => new NotificationChannelManager($app));
        $this->app->alias(NotificationChannelManager::class, 'notifications-channels');
        $this->app->singleton(PlanLimitsEnforcer::class);
    }

    public function boot(): void
    {
        Gate::before(function ($user, $ability) {
            // Check if super_admin role exists globally or in current team
            // We use a direct DB query to avoid any caching or scoping issues with Spatie's hasRole
            $hasSuperAdmin = \Illuminate\Support\Facades\DB::table(config('permission.table_names.model_has_roles'))
                ->join(config('permission.table_names.roles'), 'role_id', '=', 'id')
                ->where('model_id', $user->id)
                ->where('model_type', get_class($user))
                ->where('name', 'super_admin')
                ->exists();

            if ($hasSuperAdmin) {
                return true;
            }
            
            return null;
        });

        Campus::observe(ContentObserver::class);
        Sermon::observe(ContentObserver::class);
        Event::observe(ContentObserver::class);
        Announcement::observe(ContentObserver::class);
        Member::observe(ContentObserver::class);
        Gallery::observe(ContentObserver::class);
        Page::observe(ContentObserver::class);
        GivingRecord::observe(ContentObserver::class);

        EventFacade::listen(ContentChanged::class, NotifyTenantAdmins::class);

        Livewire::component('ai-chat', \App\Livewire\AiChat::class);
    }
}
