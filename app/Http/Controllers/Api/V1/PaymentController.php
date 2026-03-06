<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\InitiatePaymentRequest;
use App\Http\Resources\PaymentTransactionCollection;
use App\Http\Resources\PaymentTransactionResource;
use App\Jobs\ProcessPaymentWebhook;
use App\Models\PaymentTransaction;
use App\Models\Tenant;
use App\Services\Payment\PaymentManager;
use App\Services\Payment\PaymentRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(Request $request): PaymentTransactionCollection
    {
        $query = PaymentTransaction::query();

        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->has('provider')) {
            $query->where('provider', $request->input('provider'));
        }

        if ($request->has('campus_id')) {
            $query->where('campus_id', $request->input('campus_id'));
        }

        $transactions = $query
            ->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 15));

        return new PaymentTransactionCollection($transactions);
    }

    public function initiate(InitiatePaymentRequest $request, PaymentManager $manager): JsonResponse
    {
        $validated = $request->validated();
        $provider = $validated['provider'] ?? $manager->getDefaultDriver();

        $transaction = PaymentTransaction::create([
            'tenant_id' => tenant('id'),
            'member_id' => $validated['member_id'] ?? null,
            'campus_id' => $validated['campus_id'] ?? null,
            'amount' => $validated['amount'],
            'currency' => $validated['currency'] ?? config('payments.default_currency', 'XOF'),
            'provider' => $provider,
            'payment_method' => $validated['payment_method'] ?? null,
            'phone_number' => $validated['phone_number'] ?? null,
            'campaign_id' => $validated['campaign_id'] ?? null,
            'status' => 'pending',
        ]);

        $paymentRequest = new PaymentRequest(
            amount: (float) $transaction->amount,
            currency: $transaction->currency,
            phone: $transaction->phone_number,
            paymentMethod: $transaction->payment_method,
            returnUrl: $validated['return_url'] ?? null,
            notifyUrl: route('api.payments.webhook', ['provider' => $provider]),
            description: $validated['description'] ?? null,
            transactionId: $transaction->uuid,
        );

        $response = $manager->driver($provider)->initiate($paymentRequest);

        $transaction->update([
            'status' => $response->status,
            'provider_reference' => $response->providerReference,
            'provider_metadata' => $response->providerMetadata,
        ]);

        if ($response->status === 'failed') {
            $transaction->markAsFailed($response->failureReason ?? 'Payment initiation failed');
        }

        return response()->json([
            'data' => new PaymentTransactionResource($transaction->fresh()),
            'payment_url' => $response->paymentUrl,
        ], $response->status === 'failed' ? 422 : 201);
    }

    public function show(string $uuid): PaymentTransactionResource
    {
        $transaction = PaymentTransaction::where('uuid', $uuid)->firstOrFail();

        return new PaymentTransactionResource($transaction);
    }

    public function webhook(Request $request, PaymentManager $manager, string $provider): JsonResponse
    {
        $response = $manager->driver($provider)->handleWebhook($request);

        $transactionId = $response->providerMetadata['transaction_id']
            ?? $response->providerReference
            ?? $request->input('cpm_trans_id')
            ?? $request->input('transaction_id');

        if ($transactionId) {
            // No tenant context on webhook routes (external providers don't send X-Tenant-ID).
            // Query without BelongsToTenant scope, then initialize tenant from the transaction.
            $transaction = PaymentTransaction::withoutGlobalScopes()
                ->where(function ($q) use ($transactionId): void {
                    $q->where('uuid', $transactionId)
                        ->orWhere('provider_reference', $transactionId);
                })->first();

            if ($transaction) {
                $tenant = Tenant::find($transaction->tenant_id);
                if ($tenant) {
                    tenancy()->initialize($tenant);
                }
                ProcessPaymentWebhook::dispatch($transaction);
            }
        }

        return response()->json(['status' => 'received']);
    }
}
