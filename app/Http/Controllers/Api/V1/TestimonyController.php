<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreTestimonyRequest;
use App\Http\Requests\Api\V1\UpdateTestimonyRequest;
use App\Http\Resources\TestimonyCollection;
use App\Http\Resources\TestimonyResource;
use App\Models\Testimony;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TestimonyController extends Controller
{
    /**
     * List testimonies.
     *
     * Retrieve a paginated list of testimonies.
     *
     * @queryParam status string Filter by status (submitted, approved, rejected, featured).
     * @queryParam category string Filter by category (healing, provision, deliverance, conversion, family_restoration, other).
     * @queryParam featured boolean Filter for featured testimonies. Example: true
     * @queryParam per_page int Number of items per page. Example: 15
     */
    public function index(Request $request): TestimonyCollection
    {
        $this->authorize('viewAny', Testimony::class);

        $query = Testimony::with('member');

        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->has('category')) {
            $query->ofCategory($request->input('category'));
        }

        if ($request->boolean('featured', false)) {
            $query->featured();
        }

        $testimonies = $query
            ->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 15));

        return new TestimonyCollection($testimonies);
    }

    /**
     * Create testimony.
     *
     * Submit a new testimony for moderation.
     */
    public function store(StoreTestimonyRequest $request): JsonResponse
    {
        $this->authorize('create', Testimony::class);

        $testimony = Testimony::create([
            ...$request->validated(),
            'tenant_id' => tenant('id'),
        ]);

        return (new TestimonyResource($testimony->fresh()->load('member')))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Retrieve testimony.
     *
     * Get details of a specific testimony.
     */
    public function show(Testimony $testimony): TestimonyResource
    {
        $this->authorize('view', $testimony);

        return new TestimonyResource($testimony->load('member'));
    }

    /**
     * Update testimony.
     *
     * Update an existing testimony (including moderation status).
     */
    public function update(UpdateTestimonyRequest $request, Testimony $testimony): TestimonyResource
    {
        $this->authorize('update', $testimony);

        $testimony->update($request->validated());

        return new TestimonyResource($testimony->fresh()->load('member'));
    }

    /**
     * Delete testimony.
     *
     * Remove a testimony from the system.
     */
    public function destroy(Testimony $testimony): JsonResponse
    {
        $this->authorize('delete', $testimony);

        $testimony->delete();

        return response()->json(null, 204);
    }

    /**
     * React to testimony.
     *
     * Add a culturally appropriate reaction (amen, gloire_a_dieu, alleluia).
     */
    public function react(Request $request, Testimony $testimony): JsonResponse
    {
        $this->authorize('view', $testimony);

        $request->validate([
            'type' => ['required', 'string', Rule::in(['amen', 'gloire_a_dieu', 'alleluia'])],
        ]);

        $testimony->addReaction($request->input('type'));

        return response()->json([
            'reactions' => $testimony->fresh()->reactions,
            'reaction_count' => $testimony->fresh()->reaction_count,
        ]);
    }
}
