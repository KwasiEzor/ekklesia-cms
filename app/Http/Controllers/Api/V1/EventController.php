<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreEventRequest;
use App\Http\Requests\Api\V1\UpdateEventRequest;
use App\Http\Resources\EventCollection;
use App\Http\Resources\EventResource;
use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index(Request $request): EventCollection
    {
        $query = Event::query();

        if ($request->has('location')) {
            $query->where('location', 'ilike', '%' . $request->input('location') . '%');
        }

        if ($request->boolean('upcoming', false)) {
            $query->where('start_at', '>', now());
        }

        if ($request->boolean('past', false)) {
            $query->where('start_at', '<=', now());
        }

        $events = $query
            ->orderBy('start_at', 'desc')
            ->paginate($request->input('per_page', 15));

        return new EventCollection($events);
    }

    public function store(StoreEventRequest $request): EventResource
    {
        $event = Event::create([
            ...$request->validated(),
            'tenant_id' => tenant('id'),
        ]);

        return new EventResource($event);
    }

    public function show(Event $event): EventResource
    {
        return new EventResource($event);
    }

    public function update(UpdateEventRequest $request, Event $event): EventResource
    {
        $event->update($request->validated());

        return new EventResource($event->fresh());
    }

    public function destroy(Event $event): JsonResponse
    {
        $event->delete();

        return response()->json(null, 204);
    }
}
