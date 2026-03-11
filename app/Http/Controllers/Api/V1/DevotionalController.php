<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreDevotionalRequest;
use App\Http\Requests\Api\V1\UpdateDevotionalRequest;
use App\Http\Resources\DevotionalCollection;
use App\Http\Resources\DevotionalResource;
use App\Models\Devotional;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DevotionalController extends Controller
{
    /**
     * List devotionals.
     *
     * Retrieve a paginated list of devotionals.
     *
     * @queryParam status string Filter by status (draft, scheduled, published).
     * @queryParam series_id int Filter by series ID.
     * @queryParam date string Filter by scheduled date. Example: 2026-03-15
     * @queryParam today boolean Get today's devotional. Example: true
     * @queryParam per_page int Number of items per page. Example: 15
     */
    public function index(Request $request): DevotionalCollection
    {
        $this->authorize('viewAny', Devotional::class);

        $query = Devotional::with('series');

        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->has('series_id')) {
            $query->where('series_id', (int) $request->input('series_id'));
        }

        if ($request->has('date')) {
            $query->forDate($request->input('date'));
        }

        if ($request->boolean('today', false)) {
            $query->today();
        }

        $devotionals = $query
            ->orderBy('scheduled_date', 'desc')
            ->paginate($request->input('per_page', 15));

        return new DevotionalCollection($devotionals);
    }

    /**
     * Create devotional.
     *
     * Create a new devotional entry.
     */
    public function store(StoreDevotionalRequest $request): DevotionalResource
    {
        $this->authorize('create', Devotional::class);

        $devotional = Devotional::create([
            ...$request->validated(),
            'tenant_id' => tenant('id'),
        ]);

        return new DevotionalResource($devotional->load('series'));
    }

    /**
     * Retrieve devotional.
     *
     * Get details of a specific devotional.
     */
    public function show(Devotional $devotional): DevotionalResource
    {
        $this->authorize('view', $devotional);

        return new DevotionalResource($devotional->load('series'));
    }

    /**
     * Update devotional.
     *
     * Update an existing devotional.
     */
    public function update(UpdateDevotionalRequest $request, Devotional $devotional): DevotionalResource
    {
        $this->authorize('update', $devotional);

        $devotional->update($request->validated());

        return new DevotionalResource($devotional->fresh()->load('series'));
    }

    /**
     * Delete devotional.
     *
     * Remove a devotional from the system.
     */
    public function destroy(Devotional $devotional): JsonResponse
    {
        $this->authorize('delete', $devotional);

        $devotional->delete();

        return response()->json(null, 204);
    }
}
