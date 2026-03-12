<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\HouseholdCollection;
use App\Http\Resources\HouseholdResource;
use App\Models\Household;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class HouseholdController extends Controller
{
    /**
     * List households.
     *
     * Retrieve a paginated list of households.
     *
     * @queryParam search string Search by name or city. Example: Mbarga
     * @queryParam per_page int Number of items per page. Example: 15
     */
    public function index(Request $request): HouseholdCollection
    {
        $this->authorize('viewAny', Household::class);

        $query = Household::query()->withCount('members');

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('city', 'ilike', "%{$search}%");
            });
        }

        $households = $query
            ->orderBy('name')
            ->paginate($request->input('per_page', 15));

        return new HouseholdCollection($households);
    }

    /**
     * Create household.
     *
     * Create a new household.
     */
    public function store(Request $request): HouseholdResource
    {
        $this->authorize('create', Household::class);

        $validated = $request->validate([
            /** @example "Famille Mbarga" */
            'name' => ['required', 'string', 'max:255'],
            /** @example "Quartier Bastos, Yaoundé" */
            'address' => ['nullable', 'string', 'max:500'],
            /** @example "Yaoundé" */
            'city' => ['nullable', 'string', 'max:255'],
            /** @example "+237 6XX XXX XXX" */
            'phone' => ['nullable', 'string', 'max:50'],
            'custom_fields' => ['nullable', 'array'],
        ]);

        $household = Household::create([
            ...$validated,
            'tenant_id' => tenant('id'),
        ]);

        return new HouseholdResource($household);
    }

    /**
     * Retrieve household.
     *
     * Get the details of a specific household with its members.
     */
    public function show(Household $household): HouseholdResource
    {
        $this->authorize('view', $household);

        return new HouseholdResource($household->load('members'));
    }

    /**
     * Update household.
     *
     * Update an existing household.
     */
    public function update(Request $request, Household $household): HouseholdResource
    {
        $this->authorize('update', $household);

        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'slug' => [
                'sometimes', 'required', 'string', 'max:255',
                Rule::unique('households', 'slug')
                    ->where('tenant_id', tenant('id'))
                    ->ignore($household),
            ],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'custom_fields' => ['nullable', 'array'],
        ]);

        $household->update($validated);

        return new HouseholdResource($household->fresh());
    }

    /**
     * Delete household.
     *
     * Remove a household. Members will be unlinked but not deleted.
     */
    public function destroy(Household $household): JsonResponse
    {
        $this->authorize('delete', $household);

        $household->delete();

        return response()->json(null, 204);
    }
}
