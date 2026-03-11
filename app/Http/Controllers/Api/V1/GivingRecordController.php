<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreGivingRecordRequest;
use App\Http\Requests\Api\V1\UpdateGivingRecordRequest;
use App\Http\Resources\GivingRecordCollection;
use App\Http\Resources\GivingRecordResource;
use App\Models\GivingRecord;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GivingRecordController extends Controller
{
    /**
     * List giving records.
     *
     * Retrieve a paginated list of tithes, offerings, and donations.
     *
     * @queryParam campus_id int Filter by campus ID.
     * @queryParam method string Filter by payment method. Example: card
     * @queryParam currency string Filter by currency. Example: USD
     * @queryParam member_id int Filter by member ID.
     * @queryParam anonymous boolean Filter for anonymous giving. Example: false
     * @queryParam campaign_id int Filter by campaign ID.
     * @queryParam from string Filter records from date. Example: 2024-01-01
     * @queryParam to string Filter records up to date. Example: 2024-12-31
     * @queryParam per_page int Number of items per page. Example: 15
     */
    public function index(Request $request): GivingRecordCollection
    {
        $this->authorize('viewAny', GivingRecord::class);

        $query = GivingRecord::with(['member', 'fund', 'campaign']);

        if ($request->has('campus_id')) {
            $query->forCampus((int) $request->input('campus_id'));
        }

        if ($request->has('method')) {
            $query->where('method', $request->input('method'));
        }

        if ($request->has('currency')) {
            $query->where('currency', $request->input('currency'));
        }

        if ($request->has('member_id')) {
            $query->where('member_id', $request->input('member_id'));
        }

        if ($request->boolean('anonymous', false)) {
            $query->whereNull('member_id');
        }

        if ($request->has('fund_id')) {
            $query->where('fund_id', (int) $request->input('fund_id'));
        }

        if ($request->has('campaign_id')) {
            $query->where('campaign_id', (int) $request->input('campaign_id'));
        }

        if ($request->has('from')) {
            $query->where('date', '>=', $request->input('from'));
        }

        if ($request->has('to')) {
            $query->where('date', '<=', $request->input('to'));
        }

        $records = $query
            ->orderBy('date', 'desc')
            ->paginate($request->input('per_page', 15));

        return new GivingRecordCollection($records);
    }

    /**
     * Create giving record.
     *
     * Manually record a new giving transaction.
     */
    public function store(StoreGivingRecordRequest $request): GivingRecordResource
    {
        $this->authorize('create', GivingRecord::class);

        $record = GivingRecord::create([
            ...$request->validated(),
            'tenant_id' => tenant('id'),
        ]);

        return new GivingRecordResource($record->load(['member', 'fund', 'campaign']));
    }

    /**
     * Retrieve giving record.
     *
     * Get details of a specific transaction.
     */
    public function show(GivingRecord $givingRecord): GivingRecordResource
    {
        $this->authorize('view', $givingRecord);

        return new GivingRecordResource($givingRecord->load(['member', 'fund', 'campaign']));
    }

    /**
     * Update giving record.
     *
     * Update details of an existing giving record.
     */
    public function update(UpdateGivingRecordRequest $request, GivingRecord $givingRecord): GivingRecordResource
    {
        $this->authorize('update', $givingRecord);

        $givingRecord->update($request->validated());

        return new GivingRecordResource($givingRecord->fresh()->load('member'));
    }

    /**
     * Delete giving record.
     *
     * Remove a giving record from the system.
     */
    public function destroy(GivingRecord $givingRecord): JsonResponse
    {
        $this->authorize('delete', $givingRecord);

        $givingRecord->delete();

        return response()->json(null, 204);
    }
}
