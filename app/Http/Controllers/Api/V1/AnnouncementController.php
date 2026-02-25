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
    public function index(Request $request): AnnouncementCollection
    {
        $query = Announcement::query();

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

    public function store(StoreAnnouncementRequest $request): AnnouncementResource
    {
        $announcement = Announcement::create([
            ...$request->validated(),
            'tenant_id' => tenant('id'),
        ]);

        return new AnnouncementResource($announcement);
    }

    public function show(Announcement $announcement): AnnouncementResource
    {
        return new AnnouncementResource($announcement);
    }

    public function update(UpdateAnnouncementRequest $request, Announcement $announcement): AnnouncementResource
    {
        $announcement->update($request->validated());

        return new AnnouncementResource($announcement->fresh());
    }

    public function destroy(Announcement $announcement): JsonResponse
    {
        $announcement->delete();

        return response()->json(null, 204);
    }
}
