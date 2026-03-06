<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreGalleryRequest;
use App\Http\Requests\Api\V1\UpdateGalleryRequest;
use App\Http\Resources\GalleryCollection;
use App\Http\Resources\GalleryResource;
use App\Models\Gallery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GalleryController extends Controller
{
    public function index(Request $request): GalleryCollection
    {
        $this->authorize('viewAny', Gallery::class);

        $query = Gallery::query();

        if ($request->has('galleryable_type') && $request->has('galleryable_id')) {
            $query->where('galleryable_type', $request->input('galleryable_type'))
                ->where('galleryable_id', $request->input('galleryable_id'));
        }

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where('title', 'ilike', "%{$search}%");
        }

        $galleries = $query
            ->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 15));

        return new GalleryCollection($galleries);
    }

    public function store(StoreGalleryRequest $request): GalleryResource
    {
        $this->authorize('create', Gallery::class);

        $gallery = Gallery::create([
            ...$request->validated(),
            'tenant_id' => tenant('id'),
        ]);

        return new GalleryResource($gallery);
    }

    public function show(Gallery $gallery): GalleryResource
    {
        $this->authorize('view', $gallery);

        return new GalleryResource($gallery);
    }

    public function update(UpdateGalleryRequest $request, Gallery $gallery): GalleryResource
    {
        $this->authorize('update', $gallery);

        $gallery->update($request->validated());

        return new GalleryResource($gallery->fresh());
    }

    public function destroy(Gallery $gallery): JsonResponse
    {
        $this->authorize('delete', $gallery);

        $gallery->delete();

        return response()->json(null, 204);
    }
}
