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
    /**
     * List pages.
     *
     * Retrieve a paginated list of pages.
     *
     * @queryParam published boolean Filter to show only published pages. Example: true
     * @queryParam search string Search pages by title.
     * @queryParam per_page int Number of items per page. Example: 15
     */
    public function index(Request $request): PageCollection
    {
        $this->authorize('viewAny', Page::class);

        $query = Page::query();

        if ($request->boolean('published', false)) {
            $query->whereNotNull('published_at')
                ->where('published_at', '<=', now());
        }

        if ($request->has('search')) {
            $query->where('title', 'ilike', '%'.$request->input('search').'%');
        }

        $pages = $query
            ->orderByRaw('published_at DESC NULLS LAST')
            ->paginate($request->input('per_page', 15));

        return new PageCollection($pages);
    }

    /**
     * Create page.
     *
     * Create a new page.
     */
    public function store(StorePageRequest $request): PageResource
    {
        $this->authorize('create', Page::class);

        $page = Page::create([
            ...$request->validated(),
            'tenant_id' => tenant('id'),
        ]);

        return new PageResource($page);
    }

    /**
     * Retrieve page.
     *
     * Get the details of a specific page.
     */
    public function show(Page $page): PageResource
    {
        $this->authorize('view', $page);

        return new PageResource($page);
    }

    /**
     * Update page.
     *
     * Update an existing page.
     */
    public function update(UpdatePageRequest $request, Page $page): PageResource
    {
        $this->authorize('update', $page);

        $page->update($request->validated());

        return new PageResource($page->fresh());
    }

    /**
     * Delete page.
     *
     * Remove a page from the system.
     */
    public function destroy(Page $page): JsonResponse
    {
        $this->authorize('delete', $page);

        $page->delete();

        return response()->json(null, 204);
    }
}
