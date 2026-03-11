<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreAttendanceRequest;
use App\Http\Requests\Api\V1\UpdateAttendanceRequest;
use App\Http\Resources\AttendanceCollection;
use App\Http\Resources\AttendanceResource;
use App\Models\Attendance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    /**
     * List attendances.
     *
     * Retrieve a paginated list of attendance records.
     *
     * @queryParam date string Filter by specific date. Example: 2026-03-09
     * @queryParam service_type_id int Filter by service type.
     * @queryParam member_id int Filter by member.
     * @queryParam campus_id int Filter by campus.
     * @queryParam is_first_time boolean Filter first-time visitors. Example: true
     * @queryParam date_from string Filter from date. Example: 2026-03-01
     * @queryParam date_to string Filter to date. Example: 2026-03-31
     * @queryParam per_page int Number of items per page. Example: 15
     */
    public function index(Request $request): AttendanceCollection
    {
        $this->authorize('viewAny', Attendance::class);

        $query = Attendance::query()->with(['member', 'serviceType']);

        if ($request->has('date')) {
            $query->forDate($request->input('date'));
        }

        if ($request->has('service_type_id')) {
            $query->forServiceType((int) $request->input('service_type_id'));
        }

        if ($request->has('member_id')) {
            $query->forMember((int) $request->input('member_id'));
        }

        if ($request->has('campus_id')) {
            $query->forCampus((int) $request->input('campus_id'));
        }

        if ($request->boolean('is_first_time', false)) {
            $query->firstTimers();
        }

        if ($request->has('date_from') && $request->has('date_to')) {
            $query->forDateRange($request->input('date_from'), $request->input('date_to'));
        }

        $attendances = $query
            ->orderBy('date', 'desc')
            ->orderBy('checked_in_at', 'desc')
            ->paginate($request->input('per_page', 15));

        return new AttendanceCollection($attendances);
    }

    /**
     * Record attendance.
     *
     * Create a new attendance record.
     */
    public function store(StoreAttendanceRequest $request): AttendanceResource
    {
        $this->authorize('create', Attendance::class);

        $attendance = Attendance::create([
            ...$request->validated(),
            'tenant_id' => tenant('id'),
        ]);

        return new AttendanceResource($attendance->load(['member', 'serviceType']));
    }

    /**
     * Retrieve attendance.
     *
     * Get the details of a specific attendance record.
     */
    public function show(Attendance $attendance): AttendanceResource
    {
        $this->authorize('view', $attendance);

        return new AttendanceResource($attendance->load(['member', 'serviceType']));
    }

    /**
     * Update attendance.
     *
     * Update an existing attendance record.
     */
    public function update(UpdateAttendanceRequest $request, Attendance $attendance): AttendanceResource
    {
        $this->authorize('update', $attendance);

        $attendance->update($request->validated());

        return new AttendanceResource($attendance->fresh()->load(['member', 'serviceType']));
    }

    /**
     * Delete attendance.
     *
     * Remove an attendance record.
     */
    public function destroy(Attendance $attendance): JsonResponse
    {
        $this->authorize('delete', $attendance);

        $attendance->delete();

        return response()->json(null, 204);
    }
}
