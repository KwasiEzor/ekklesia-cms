<?php

namespace App\Jobs;

use App\Events\PaymentCompleted;
use App\Models\GivingRecord;
use App\Models\PaymentTransaction;
use App\Models\Tenant;
use App\Services\Payment\PaymentManager;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ProcessPaymentWebhook implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 30;

    public function __construct(
        public PaymentTransaction $transaction,
    ) {
        $this->queue = 'payments';
    }

    public function handle(PaymentManager $manager): void
    {
        // Ensure tenant context is initialized for GivingRecord creation and scoped queries.
        $tenant = Tenant::find($this->transaction->tenant_id);
        if ($tenant) {
            tenancy()->initialize($tenant);
        }

        if ($this->transaction->status === 'completed') {
            return;
        }

        $response = $manager->driver($this->transaction->provider)
            ->checkStatus($this->transaction->provider_reference ?? $this->transaction->uuid);

        $this->transaction->update([
            'status' => $response->status,
            'provider_reference' => $response->providerReference ?? $this->transaction->provider_reference,
            'provider_metadata' => $response->providerMetadata,
        ]);

        if ($response->status === 'completed') {
            $this->transaction->update(['paid_at' => now()]);
            $this->createGivingRecord();
            PaymentCompleted::dispatch($this->transaction->fresh());
        } elseif ($response->status === 'failed') {
            $this->transaction->update([
                'failed_at' => now(),
                'failure_reason' => $response->failureReason,
            ]);
        }
    }

    private function createGivingRecord(): void
    {
        if ($this->transaction->giving_record_id) {
            return;
        }

        try {
            $record = GivingRecord::create([
                'tenant_id' => $this->transaction->tenant_id,
                'member_id' => $this->transaction->member_id,
                'campus_id' => $this->transaction->campus_id,
                'amount' => $this->transaction->amount,
                'currency' => $this->transaction->currency,
                'date' => $this->transaction->paid_at ?? now(),
                'method' => $this->transaction->payment_method ?? $this->transaction->provider,
                'reference' => $this->transaction->uuid,
                'campaign_id' => $this->transaction->campaign_id,
            ]);

            $this->transaction->update(['giving_record_id' => $record->id]);
        } catch (\Throwable $e) {
            Log::error('Failed to create GivingRecord from payment', [
                'transaction_uuid' => $this->transaction->uuid,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
