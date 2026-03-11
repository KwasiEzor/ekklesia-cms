<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreBulkMessageRequest;
use App\Http\Resources\BulkMessageCollection;
use App\Http\Resources\BulkMessageResource;
use App\Jobs\SendBulkMessageJob;
use App\Models\BulkMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BulkMessageController extends Controller
{
    /**
     * List bulk messages.
     *
     * Retrieve a paginated list of bulk messages.
     *
     * @queryParam status string Filter by status (draft, scheduled, sending, sent, failed).
     * @queryParam channel string Filter by channel (sms, email, whatsapp).
     * @queryParam per_page int Number of items per page. Example: 15
     */
    public function index(Request $request): BulkMessageCollection
    {
        $this->authorize('viewAny', BulkMessage::class);

        $query = BulkMessage::query();

        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->has('channel')) {
            $query->where('channel', $request->input('channel'));
        }

        $messages = $query
            ->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 15));

        return new BulkMessageCollection($messages);
    }

    /**
     * Create bulk message.
     *
     * Create a new bulk message (draft or scheduled).
     */
    public function store(StoreBulkMessageRequest $request): JsonResponse
    {
        $this->authorize('create', BulkMessage::class);

        $data = $request->validated();
        $data['tenant_id'] = tenant('id');
        $data['created_by'] = auth()->id();
        $data['status'] = isset($data['scheduled_at']) ? 'scheduled' : 'draft';

        $message = BulkMessage::create($data);

        return (new BulkMessageResource($message))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Retrieve bulk message.
     *
     * Get details of a specific bulk message.
     */
    public function show(BulkMessage $bulkMessage): BulkMessageResource
    {
        $this->authorize('view', $bulkMessage);

        return new BulkMessageResource($bulkMessage);
    }

    /**
     * Delete bulk message.
     *
     * Remove a bulk message (only if draft or scheduled).
     */
    public function destroy(BulkMessage $bulkMessage): JsonResponse
    {
        $this->authorize('delete', $bulkMessage);

        if (in_array($bulkMessage->status, ['sending', 'sent'])) {
            return response()->json([
                'message' => __('bulk_messages.cannot_delete_sent'),
            ], 422);
        }

        $bulkMessage->delete();

        return response()->json(null, 204);
    }

    /**
     * Send bulk message now.
     *
     * Immediately dispatch the bulk message to all recipients.
     */
    public function send(BulkMessage $bulkMessage): JsonResponse
    {
        $this->authorize('update', $bulkMessage);

        if (! in_array($bulkMessage->status, ['draft', 'scheduled'])) {
            return response()->json([
                'message' => __('bulk_messages.cannot_send_status'),
            ], 422);
        }

        SendBulkMessageJob::dispatch($bulkMessage);

        return response()->json([
            'message' => __('bulk_messages.sending'),
            'data' => new BulkMessageResource($bulkMessage->fresh()),
        ]);
    }
}
