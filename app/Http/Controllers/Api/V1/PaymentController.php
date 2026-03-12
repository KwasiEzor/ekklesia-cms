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
use App\Services\Payment\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    /**
     * List payment transactions.
     *
     * Retrieve a paginated list of payment transactions.
     *
     * @queryParam status string Filter by transaction status (e.g., pending, successful, failed). Example: successful
     * @queryParam provider string Filter by payment provider. Example: chapa
     * @queryParam campus_id int Filter by campus ID.
     * @queryParam per_page int Number of items per page. Example: 15
     */
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

    /**
     * Initiate payment.
     *
     * Start a new payment transaction with the specified provider. Returns the checkout URL.
     */
    public function initiate(InitiatePaymentRequest $request, PaymentService $service): JsonResponse
    {
        $result = $service->initiatePayment($request->validated());

        return response()->json([
            'data' => new PaymentTransactionResource($result['transaction']),
            'payment_url' => $result['payment_url'],
        ], $result['status'] === 'failed' ? 422 : 201);
    }

    /**
     * Retrieve payment transaction.
     *
     * Get details of a specific payment transaction by its UUID.
     *
     * @urlParam uuid string The UUID of the transaction.
     */
    public function show(string $uuid): PaymentTransactionResource
    {
        $transaction = PaymentTransaction::where('uuid', $uuid)->firstOrFail();

        return new PaymentTransactionResource($transaction);
    }

    /**
     * Payment webhook.
     *
     * Webhook endpoint for payment providers to send asynchronous transaction updates.
     * This endpoint is meant for server-to-server communication and is publicly accessible.
     *
     * @unauthenticated
     *
     * @urlParam provider string The payment provider (e.g., chapa, paystack).
     */
    public function webhook(Request $request, PaymentManager $manager, string $provider): JsonResponse
    {
        $response = $manager->driver($provider)->handleWebhook($request);

        if ($response->status === 'failed') {
            return response()->json(['status' => 'error', 'message' => $response->failureReason], 400);
        }

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
