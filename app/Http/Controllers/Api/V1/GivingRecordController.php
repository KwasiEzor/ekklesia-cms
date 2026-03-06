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
    public function index(Request $request): GivingRecordCollection
    {
        $this->authorize('viewAny', GivingRecord::class);

        $query = GivingRecord::with('member');

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

        if ($request->has('campaign_id')) {
            $query->where('campaign_id', $request->input('campaign_id'));
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

    public function store(StoreGivingRecordRequest $request): GivingRecordResource
    {
        $this->authorize('create', GivingRecord::class);

        $record = GivingRecord::create([
            ...$request->validated(),
            'tenant_id' => tenant('id'),
        ]);

        return new GivingRecordResource($record->load('member'));
    }

    public function show(GivingRecord $givingRecord): GivingRecordResource
    {
        $this->authorize('view', $givingRecord);

        return new GivingRecordResource($givingRecord->load('member'));
    }

    public function update(UpdateGivingRecordRequest $request, GivingRecord $givingRecord): GivingRecordResource
    {
        $this->authorize('update', $givingRecord);

        $givingRecord->update($request->validated());

        return new GivingRecordResource($givingRecord->fresh()->load('member'));
    }

    public function destroy(GivingRecord $givingRecord): JsonResponse
    {
        $this->authorize('delete', $givingRecord);

        $givingRecord->delete();

        return response()->json(null, 204);
    }
}
