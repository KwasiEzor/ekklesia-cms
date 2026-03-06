<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreSermonRequest;
use App\Http\Requests\Api\V1\UpdateSermonRequest;
use App\Http\Resources\SermonCollection;
use App\Http\Resources\SermonResource;
use App\Models\Sermon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SermonController extends Controller
{
    public function index(Request $request): SermonCollection
    {
        $this->authorize('viewAny', Sermon::class);

        $query = Sermon::query()->with('series');

        if ($request->has('campus_id')) {
            $query->forCampus((int) $request->input('campus_id'));
        }

        if ($request->has('speaker')) {
            $query->where('speaker', $request->input('speaker'));
        }

        if ($request->has('series_id')) {
            $query->where('series_id', $request->input('series_id'));
        }

        if ($request->has('tag')) {
            $query->withAnyTags([$request->input('tag')]);
        }

        $sermons = $query
            ->orderByDesc('date')
            ->paginate($request->input('per_page', 15));

        return new SermonCollection($sermons);
    }

    public function store(StoreSermonRequest $request): SermonResource
    {
        $this->authorize('create', Sermon::class);

        $validated = $request->validated();
        $tags = $validated['tags'] ?? [];
        unset($validated['tags']);

        $sermon = Sermon::create([
            ...$validated,
            'tenant_id' => tenant('id'),
        ]);

        if ($tags) {
            $sermon->syncTags($tags);
        }

        return new SermonResource($sermon->load('series'));
    }

    public function show(Sermon $sermon): SermonResource
    {
        $this->authorize('view', $sermon);

        return new SermonResource($sermon->load('series'));
    }

    public function update(UpdateSermonRequest $request, Sermon $sermon): SermonResource
    {
        $this->authorize('update', $sermon);

        $validated = $request->validated();
        $tags = $validated['tags'] ?? null;
        unset($validated['tags']);

        $sermon->update($validated);

        if ($tags !== null) {
            $sermon->syncTags($tags);
        }

        return new SermonResource($sermon->fresh()->load('series'));
    }

    public function destroy(Sermon $sermon): JsonResponse
    {
        $this->authorize('delete', $sermon);

        $sermon->delete();

        return response()->json(null, 204);
    }
}
