<?php

namespace App\Jobs;

use App\Models\PaymentTransaction;
use App\Models\Tenant;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class CheckPendingPayments implements ShouldQueue
{
    use Queueable;

    public int $timeout = 120;

    public function __construct()
    {
        $this->queue = 'payments';
    }

    public function handle(): void
    {
        // Job runs from scheduler without tenant context.
        // Use withoutGlobalScopes() to bypass BelongsToTenant scope.
        $staleTransactions = PaymentTransaction::withoutGlobalScopes()
            ->whereIn('status', ['pending', 'processing'])
            ->where('created_at', '<', now()->subMinutes(config('payments.pending_check_interval', 5)))
            ->where('created_at', '>', now()->subHours(24))
            ->limit(50)
            ->get();

        foreach ($staleTransactions as $transaction) {
            try {
                // Initialize tenant context before dispatching
                $tenant = Tenant::find($transaction->tenant_id);
                if ($tenant) {
                    tenancy()->initialize($tenant);
                }
                ProcessPaymentWebhook::dispatch($transaction);
            } catch (\Throwable $e) {
                Log::error('Failed to dispatch payment check', [
                    'transaction_uuid' => $transaction->uuid,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
