<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StorePageRequest;
use App\Http\Requests\Api\V1\UpdatePageRequest;
use App\Http\Resources\PageCollection;
use App\Http\Resources\PageResource;
use App\Models\Page;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function index(Request $request): PageCollection
    {
        $query = Page::query();

        if ($request->boolean('published', false)) {
            $query->whereNotNull('published_at')
                ->where('published_at', '<=', now());
        }

        if ($request->has('search')) {
            $query->where('title', 'ilike', '%' . $request->input('search') . '%');
        }

        $pages = $query
            ->orderByRaw('published_at DESC NULLS LAST')
            ->paginate($request->input('per_page', 15));

        return new PageCollection($pages);
    }

    public function store(StorePageRequest $request): PageResource
    {
        $page = Page::create([
            ...$request->validated(),
            'tenant_id' => tenant('id'),
        ]);

        return new PageResource($page);
    }

    public function show(Page $page): PageResource
    {
        return new PageResource($page);
    }

    public function update(UpdatePageRequest $request, Page $page): PageResource
    {
        $page->update($request->validated());

        return new PageResource($page->fresh());
    }

    public function destroy(Page $page): JsonResponse
    {
        $page->delete();

        return response()->json(null, 204);
    }
}
