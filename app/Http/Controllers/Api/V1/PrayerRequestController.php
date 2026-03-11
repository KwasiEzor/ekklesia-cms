<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PrayerRequestCollection;
use App\Http\Resources\PrayerRequestResource;
use App\Models\PrayerCommitment;
use App\Models\PrayerRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PrayerRequestController extends Controller
{
    /**
     * List prayer requests.
     *
     * Retrieve a paginated list of prayer requests.
     *
     * @queryParam type string Filter by type (prayer, praise, testimony). Example: prayer
     * @queryParam status string Filter by status (active, answered, closed). Example: active
     * @queryParam visibility string Filter by visibility. Example: public
     * @queryParam featured boolean Show only featured. Example: true
     * @queryParam per_page int Number of items per page. Example: 15
     */
    public function index(Request $request): PrayerRequestCollection
    {
        $this->authorize('viewAny', PrayerRequest::class);

        $query = PrayerRequest::query()->withCount('commitments as prayer_count');

        if ($request->has('type')) {
            $query->ofType($request->input('type'));
        }

        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->has('visibility')) {
            $query->where('visibility', $request->input('visibility'));
        }

        if ($request->boolean('featured', false)) {
            $query->featured();
        }

        $prayerRequests = $query
            ->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 15));

        return new PrayerRequestCollection($prayerRequests);
    }

    /**
     * Create prayer request.
     *
     * Submit a new prayer request.
     */
    public function store(Request $request): PrayerRequestResource
    {
        $this->authorize('create', PrayerRequest::class);

        $validated = $request->validate([
            /** @example 1 */
            'member_id' => ['required', 'integer', 'exists:members,id'],
            /** @example "prayer" */
            'type' => ['required', 'string', 'in:prayer,praise,testimony'],
            /** @example "public" */
            'visibility' => ['required', 'string', 'in:public,group,confidential'],
            /** @example "Prière pour ma guérison" */
            'title' => ['required', 'string', 'max:255'],
            /** @example "Je demande la prière pour..." */
            'content' => ['required', 'string', 'max:5000'],
            'category' => ['nullable', 'string', 'max:100'],
            'cell_group_id' => ['nullable', 'integer', 'exists:cell_groups,id'],
            'is_anonymous' => ['nullable', 'boolean'],
            'custom_fields' => ['nullable', 'array'],
        ]);

        $prayerRequest = PrayerRequest::create([
            ...$validated,
            'tenant_id' => tenant('id'),
        ]);

        return new PrayerRequestResource($prayerRequest->loadCount('commitments as prayer_count'));
    }

    /**
     * Retrieve prayer request.
     *
     * Get the details of a specific prayer request.
     */
    public function show(PrayerRequest $prayerRequest): PrayerRequestResource
    {
        $this->authorize('view', $prayerRequest);

        return new PrayerRequestResource($prayerRequest->loadCount('commitments as prayer_count'));
    }

    /**
     * Update prayer request.
     *
     * Update an existing prayer request.
     */
    public function update(Request $request, PrayerRequest $prayerRequest): PrayerRequestResource
    {
        $this->authorize('update', $prayerRequest);

        $validated = $request->validate([
            'type' => ['sometimes', 'required', 'string', 'in:prayer,praise,testimony'],
            'visibility' => ['sometimes', 'required', 'string', 'in:public,group,confidential'],
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'content' => ['sometimes', 'required', 'string', 'max:5000'],
            'category' => ['nullable', 'string', 'max:100'],
            'status' => ['sometimes', 'string', 'in:active,answered,closed'],
            'is_anonymous' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
            'testimony' => ['nullable', 'string', 'max:5000'],
            'custom_fields' => ['nullable', 'array'],
        ]);

        if (isset($validated['status']) && $validated['status'] === 'answered' && ! $prayerRequest->answered_at) {
            $validated['answered_at'] = now();
        }

        $prayerRequest->update($validated);

        return new PrayerRequestResource($prayerRequest->fresh()->loadCount('commitments as prayer_count'));
    }

    /**
     * Delete prayer request.
     *
     * Remove a prayer request.
     */
    public function destroy(PrayerRequest $prayerRequest): JsonResponse
    {
        $this->authorize('delete', $prayerRequest);

        $prayerRequest->delete();

        return response()->json(null, 204);
    }

    /**
     * Commit to pray.
     *
     * Record that a member commits to pray for this request.
     */
    public function commit(Request $request, PrayerRequest $prayerRequest): JsonResponse
    {
        $this->authorize('view', $prayerRequest);

        $validated = $request->validate([
            'member_id' => ['required', 'integer', 'exists:members,id'],
        ]);

        PrayerCommitment::firstOrCreate([
            'prayer_request_id' => $prayerRequest->id,
            'member_id' => $validated['member_id'],
            'tenant_id' => tenant('id'),
        ], [
            'prayed_at' => now(),
        ]);

        return response()->json([
            'prayer_count' => $prayerRequest->commitments()->count(),
        ]);
    }
}
