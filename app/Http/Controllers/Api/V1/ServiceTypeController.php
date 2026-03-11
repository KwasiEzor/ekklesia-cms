<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ServiceTypeCollection;
use App\Http\Resources\ServiceTypeResource;
use App\Models\ServiceType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ServiceTypeController extends Controller
{
    /**
     * List service types.
     *
     * Retrieve all service types for the current tenant.
     *
     * @queryParam active boolean Filter by active status. Example: true
     * @queryParam campus_id int Filter by campus.
     */
    public function index(Request $request): ServiceTypeCollection
    {
        $this->authorize('viewAny', ServiceType::class);

        $query = ServiceType::query();

        if ($request->has('active')) {
            $query->where('is_active', $request->boolean('active'));
        }

        if ($request->has('campus_id')) {
            $query->forCampus((int) $request->input('campus_id'));
        }

        $serviceTypes = $query
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate($request->input('per_page', 15));

        return new ServiceTypeCollection($serviceTypes);
    }

    /**
     * Create service type.
     *
     * Create a new service type.
     */
    public function store(Request $request): ServiceTypeResource
    {
        $this->authorize('create', ServiceType::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'day_of_week' => ['nullable', 'string', 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday'],
            'default_start_time' => ['nullable', 'date_format:H:i'],
            'expected_duration_minutes' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'campus_id' => ['nullable', 'integer', 'exists:campuses,id'],
            'custom_fields' => ['nullable', 'array'],
        ]);

        $serviceType = ServiceType::create([
            ...$validated,
            'tenant_id' => tenant('id'),
        ]);

        return new ServiceTypeResource($serviceType);
    }

    /**
     * Retrieve service type.
     *
     * Get the details of a specific service type.
     */
    public function show(ServiceType $serviceType): ServiceTypeResource
    {
        $this->authorize('view', $serviceType);

        return new ServiceTypeResource($serviceType);
    }

    /**
     * Update service type.
     *
     * Update an existing service type.
     */
    public function update(Request $request, ServiceType $serviceType): ServiceTypeResource
    {
        $this->authorize('update', $serviceType);

        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'slug' => [
                'sometimes', 'required', 'string', 'max:255',
                Rule::unique('service_types', 'slug')
                    ->where('tenant_id', tenant('id'))
                    ->ignore($serviceType),
            ],
            'day_of_week' => ['nullable', 'string', 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday'],
            'default_start_time' => ['nullable', 'date_format:H:i'],
            'expected_duration_minutes' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'campus_id' => ['nullable', 'integer', 'exists:campuses,id'],
            'custom_fields' => ['nullable', 'array'],
        ]);

        $serviceType->update($validated);

        return new ServiceTypeResource($serviceType->fresh());
    }

    /**
     * Delete service type.
     *
     * Remove a service type.
     */
    public function destroy(ServiceType $serviceType): JsonResponse
    {
        $this->authorize('delete', $serviceType);

        $serviceType->delete();

        return response()->json(null, 204);
    }
}
