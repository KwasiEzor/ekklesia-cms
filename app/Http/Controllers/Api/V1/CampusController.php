<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreCampusRequest;
use App\Http\Requests\Api\V1\UpdateCampusRequest;
use App\Http\Resources\CampusCollection;
use App\Http\Resources\CampusResource;
use App\Models\Campus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CampusController extends Controller
{
    public function index(Request $request): CampusCollection
    {
        $query = Campus::query();

        if ($request->has('city')) {
            $query->where('city', 'ilike', '%'.$request->input('city').'%');
        }

        if ($request->boolean('is_main', false)) {
            $query->where('is_main', true);
        }

        $campuses = $query
            ->orderBy('name')
            ->paginate($request->input('per_page', 15));

        return new CampusCollection($campuses);
    }

    public function store(StoreCampusRequest $request): CampusResource
    {
        $campus = Campus::create([
            ...$request->validated(),
            'tenant_id' => tenant('id'),
        ]);

        return new CampusResource($campus);
    }

    public function show(Campus $campus): CampusResource
    {
        return new CampusResource($campus);
    }

    public function update(UpdateCampusRequest $request, Campus $campus): CampusResource
    {
        $campus->update($request->validated());

        return new CampusResource($campus->fresh());
    }

    public function destroy(Campus $campus): JsonResponse
    {
        $campus->delete();

        return response()->json(null, 204);
    }
}
