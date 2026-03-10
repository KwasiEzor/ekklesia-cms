<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreAnnouncementRequest;
use App\Http\Requests\Api\V1\UpdateAnnouncementRequest;
use App\Http\Resources\AnnouncementCollection;
use App\Http\Resources\AnnouncementResource;
use App\Models\Announcement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    /**
     * List announcements.
     *
     * Retrieve a paginated list of announcements.
     *
     * @queryParam campus_id int Filter announcements for a specific campus ID.
     * @queryParam pinned boolean Filter for pinned (or unpinned) announcements. Example: true
     * @queryParam active boolean Filter to show currently active (published and not expired) announcements. Example: true
     * @queryParam expired boolean Filter to show only expired announcements. Example: false
     * @queryParam target_group string Filter announcements by target group. Example: youth
     * @queryParam per_page int Number of items per page. Example: 15
     */
    public function index(Request $request): AnnouncementCollection
    {
        $this->authorize('viewAny', Announcement::class);

        $query = Announcement::query();

        if ($request->has('campus_id')) {
            $query->forCampus((int) $request->input('campus_id'));
        }

        if ($request->has('pinned')) {
            $query->where('pinned', $request->boolean('pinned'));
        }

        if ($request->boolean('active', false)) {
            $query->where('published_at', '<=', now())
                ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()));
        }

        if ($request->boolean('expired', false)) {
            $query->whereNotNull('expires_at')->where('expires_at', '<=', now());
        }

        if ($request->has('target_group')) {
            $query->where('target_group', $request->input('target_group'));
        }

        $announcements = $query
            ->orderBy('published_at', 'desc')
            ->paginate($request->input('per_page', 15));

        return new AnnouncementCollection($announcements);
    }

    /**
     * Create announcement.
     *
     * Create a new announcement.
     */
    public function store(StoreAnnouncementRequest $request): AnnouncementResource
    {
        $this->authorize('create', Announcement::class);

        $announcement = Announcement::create([
            ...$request->validated(),
            'tenant_id' => tenant('id'),
        ]);

        return new AnnouncementResource($announcement);
    }

    /**
     * Retrieve announcement.
     *
     * Get the details of a specific announcement.
     */
    public function show(Announcement $announcement): AnnouncementResource
    {
        $this->authorize('view', $announcement);

        return new AnnouncementResource($announcement);
    }

    /**
     * Update announcement.
     *
     * Update an existing announcement.
     */
    public function update(UpdateAnnouncementRequest $request, Announcement $announcement): AnnouncementResource
    {
        $this->authorize('update', $announcement);

        $announcement->update($request->validated());

        return new AnnouncementResource($announcement->fresh());
    }

    /**
     * Delete announcement.
     *
     * Remove an announcement from the system.
     */
    public function destroy(Announcement $announcement): JsonResponse
    {
        $this->authorize('delete', $announcement);

        $announcement->delete();

        return response()->json(null, 204);
    }
}
