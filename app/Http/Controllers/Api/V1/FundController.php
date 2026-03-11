<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreFundRequest;
use App\Http\Requests\Api\V1\UpdateFundRequest;
use App\Http\Resources\FundCollection;
use App\Http\Resources\FundResource;
use App\Models\Fund;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FundController extends Controller
{
    /**
     * List funds.
     *
     * Retrieve a paginated list of giving funds.
     *
     * @queryParam type string Filter by fund type (tithe, offering, building, missions, benevolence, other).
     * @queryParam active boolean Filter by active status. Example: true
     * @queryParam per_page int Number of items per page. Example: 15
     */
    public function index(Request $request): FundCollection
    {
        $this->authorize('viewAny', Fund::class);

        $query = Fund::query();

        if ($request->has('type')) {
            $query->ofType($request->input('type'));
        }

        if ($request->has('active')) {
            $request->boolean('active')
                ? $query->active()
                : $query->where('is_active', false);
        }

        $funds = $query
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate($request->input('per_page', 15));

        return new FundCollection($funds);
    }

    /**
     * Create fund.
     *
     * Create a new giving fund category.
     */
    public function store(StoreFundRequest $request): FundResource
    {
        $this->authorize('create', Fund::class);

        $fund = Fund::create([
            ...$request->validated(),
            'tenant_id' => tenant('id'),
        ]);

        return new FundResource($fund);
    }

    /**
     * Retrieve fund.
     *
     * Get details of a specific fund.
     */
    public function show(Fund $fund): FundResource
    {
        $this->authorize('view', $fund);

        return new FundResource($fund);
    }

    /**
     * Update fund.
     *
     * Update an existing fund's details.
     */
    public function update(UpdateFundRequest $request, Fund $fund): FundResource
    {
        $this->authorize('update', $fund);

        $fund->update($request->validated());

        return new FundResource($fund->fresh());
    }

    /**
     * Delete fund.
     *
     * Remove a fund from the system.
     */
    public function destroy(Fund $fund): JsonResponse
    {
        $this->authorize('delete', $fund);

        $fund->delete();

        return response()->json(null, 204);
    }
}
