<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\GalleryCollection;
use App\Http\Resources\GalleryResource;
use App\Models\Gallery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class GalleryController extends Controller
{
    public function index(Request $request): GalleryCollection
    {
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

    public function store(Request $request): GalleryResource
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'galleryable_type' => ['nullable', 'string', Rule::in(['App\\Models\\Event', 'App\\Models\\Member'])],
            'galleryable_id' => ['nullable', 'integer'],
            'custom_fields' => ['nullable', 'array'],
        ]);

        $gallery = Gallery::create([
            ...$validated,
            'tenant_id' => tenant('id'),
        ]);

        return new GalleryResource($gallery);
    }

    public function show(Gallery $gallery): GalleryResource
    {
        return new GalleryResource($gallery);
    }

    public function update(Request $request, Gallery $gallery): GalleryResource
    {
        $validated = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'galleryable_type' => ['nullable', 'string', Rule::in(['App\\Models\\Event', 'App\\Models\\Member'])],
            'galleryable_id' => ['nullable', 'integer'],
            'custom_fields' => ['nullable', 'array'],
        ]);

        $gallery->update($validated);

        return new GalleryResource($gallery->fresh());
    }

    public function destroy(Gallery $gallery): JsonResponse
    {
        $gallery->delete();

        return response()->json(null, 204);
    }
}
