<?php

namespace App\Filament\Pages;

use App\Models\PlanLimit;
use App\Models\Tenant;
use App\Services\Billing\PlanLimitsEnforcer;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Collection;

class Billing extends Page
{
    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedCurrencyDollar;

    protected static ?int $navigationSort = 98;

    protected string $view = 'filament.pages.billing';

    public ?PlanLimit $currentPlan = null;

    public array $usage = [];

    /** @var Collection<int, PlanLimit> */
    public Collection $plans;

    public string $currentPlanSlug = 'free';

    public bool $isSubscribed = false;

    public bool $isCancelled = false;

    public bool $onGracePeriod = false;

    public function upgradeAction(): Action
    {
        return Action::make('upgrade')
            ->label(__('billing.upgrade'))
            ->action(function (array $arguments): mixed {
                $tenant = Filament::getTenant();
                if (! $tenant instanceof Tenant) {
                    return null;
                }

                $plan = PlanLimit::where('plan_slug', $arguments['plan'])->first();

                if (! $plan || ! $plan->stripe_price_id) {
                    Notification::make()
                        ->title(__('billing.plan_not_available'))
                        ->danger()
                        ->send();

                    return null;
                }

                return $tenant->newSubscription('default', $plan->stripe_price_id)
                    ->checkout([
                        'success_url' => route('filament.admin.pages.billing', ['tenant' => $tenant->id]),
                        'cancel_url' => route('filament.admin.pages.billing', ['tenant' => $tenant->id]),
                    ]);
            });
    }

    public function cancelAction(): Action
    {
        return Action::make('cancel')
            ->label(__('billing.cancel'))
            ->requiresConfirmation()
            ->color('danger')
            ->action(function (): void {
                $tenant = Filament::getTenant();
                if (! $tenant instanceof Tenant) {
                    return;
                }

                $tenant->subscription('default')?->cancel();

                Notification::make()
                    ->title(__('billing.subscription_cancelled'))
                    ->success()
                    ->send();

                $this->redirect(route('filament.admin.pages.billing', ['tenant' => $tenant->id]));
            });
    }

    public function resumeAction(): Action
    {
        return Action::make('resume')
            ->label(__('billing.resume'))
            ->color('success')
            ->action(function (): void {
                $tenant = Filament::getTenant();
                if (! $tenant instanceof Tenant) {
                    return;
                }

                $tenant->subscription('default')?->resume();

                Notification::make()
                    ->title(__('billing.subscription_resumed'))
                    ->success()
                    ->send();

                $this->redirect(route('filament.admin.pages.billing', ['tenant' => $tenant->id]));
            });
    }

    public function portalAction(): Action
    {
        return Action::make('portal')
            ->label(__('billing.billing_portal'))
            ->action(function (): mixed {
                $tenant = Filament::getTenant();
                if (! $tenant instanceof Tenant) {
                    return null;
                }

                return $tenant->redirectToBillingPortal(route('filament.admin.pages.billing', ['tenant' => $tenant->id]));
            });
    }

    public static function getNavigationLabel(): string
    {
        return __('billing.title');
    }

    public function getTitle(): string
    {
        return __('billing.title');
    }

    public function getSubheading(): ?string
    {
        return __('billing.description');
    }

    public function getMaxContentWidth(): Width|string|null
    {
        return Width::Full;
    }

    public function mount(): void
    {
        $tenant = Filament::getTenant();
        if (! $tenant instanceof Tenant) {
            abort(404);
        }

        $enforcer = app(PlanLimitsEnforcer::class);

        $this->currentPlan = $enforcer->getPlanLimits($tenant);
        $this->usage = $enforcer->getUsageSummary($tenant);
        $this->plans = PlanLimit::query()->get()->sortBy(fn (PlanLimit $p): int => match ($p->plan_slug) {
            'free' => 0,
            'basic' => 1,
            'premium' => 2,
            'enterprise' => 3,
            default => 4,
        })->values();
        $this->currentPlanSlug = $tenant->currentPlanSlug();

        $subscription = $tenant->subscription('default');
        $this->isSubscribed = $tenant->subscribed('default');
        $this->isCancelled = $subscription?->canceled() ?? false;
        $this->onGracePeriod = $subscription?->onGracePeriod() ?? false;
    }

    public function usagePercent(string $key): int
    {
        $data = $this->usage[$key] ?? null;

        if (! $data || $data['unlimited'] || $data['limit'] === 0) {
            return 0;
        }

        return (int) min(100, round(($data['current'] / $data['limit']) * 100));
    }

    public function usageColor(string $key): string
    {
        $percent = $this->usagePercent($key);

        if ($percent >= 90) {
            return 'red';
        }

        if ($percent >= 70) {
            return 'amber';
        }

        return 'emerald';
    }

    public function usageLabel(string $key): string
    {
        $data = $this->usage[$key] ?? null;

        if (! $data) {
            return '-';
        }

        $current = $key === 'storage_mb'
            ? $data['current'].' MB'
            : (string) $data['current'];

        if ($data['unlimited']) {
            $limit = '∞';
        } else {
            $limit = $key === 'storage_mb'
                ? $data['limit'].' MB'
                : (string) $data['limit'];
        }

        return $current.' / '.$limit;
    }

    public function planBorderColor(string $slug): string
    {
        return match ($slug) {
            'basic' => 'border-blue-500',
            'premium' => 'border-indigo-500',
            'enterprise' => 'border-emerald-500',
            default => 'border-gray-400',
        };
    }

    public function planBgAccent(string $slug): string
    {
        return match ($slug) {
            'basic' => 'bg-blue-500',
            'premium' => 'bg-indigo-500',
            'enterprise' => 'bg-emerald-500',
            default => 'bg-gray-400',
        };
    }

    public function formatPlanPrice(PlanLimit $plan): string
    {
        if ($plan->plan_slug === 'enterprise') {
            return __('billing.custom_pricing');
        }

        if ($plan->price_cents === 0) {
            return __('billing.free');
        }

        $amount = $plan->price_cents / 100;
        $currency = strtoupper((string) ($plan->currency ?? 'USD'));
        $formattedAmount = number_format($amount, 0);

        return match ($currency) {
            'USD' => '$'.$formattedAmount,
            'EUR' => '€'.$formattedAmount,
            'GBP' => '£'.$formattedAmount,
            'XOF', 'XAF' => $formattedAmount.' FCFA',
            'CAD' => $formattedAmount.' CAD',
            default => $formattedAmount.' '.$currency,
        };
    }

    public function formatPlanLimit(PlanLimit $plan, string $field): string
    {
        if ($plan->isUnlimited($field)) {
            return __('billing.unlimited');
        }

        if ($field === 'max_storage_mb') {
            $val = $plan->$field;

            return $val >= 1024
                ? round($val / 1024, 1).' GB'
                : $val.' MB';
        }

        return number_format($plan->$field);
    }
}
