<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreMemberRequest;
use App\Http\Requests\Api\V1\UpdateMemberRequest;
use App\Http\Resources\MemberCollection;
use App\Http\Resources\MemberResource;
use App\Models\Member;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    /**
     * List members.
     *
     * Retrieve a paginated list of church members.
     *
     * @queryParam campus_id int Filter by campus ID.
     * @queryParam status string Filter by membership status. Example: active
     * @queryParam cell_group_id int Filter by cell group ID.
     * @queryParam search string Search by first name, last name, or email.
     * @queryParam per_page int Number of items per page. Example: 15
     */
    public function index(Request $request): MemberCollection
    {
        $this->authorize('viewAny', Member::class);

        $query = Member::query();

        if ($request->has('campus_id')) {
            $query->forCampus((int) $request->input('campus_id'));
        }

        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->has('cell_group_id')) {
            $query->where('cell_group_id', $request->input('cell_group_id'));
        }

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search): void {
                $q->where('first_name', 'ilike', "%{$search}%")
                    ->orWhere('last_name', 'ilike', "%{$search}%")
                    ->orWhere('email', 'ilike', "%{$search}%");
            });
        }

        $members = $query
            ->orderBy('last_name', 'asc')
            ->paginate($request->input('per_page', 15));

        return new MemberCollection($members);
    }

    /**
     * Create member.
     *
     * Register a new church member.
     */
    public function store(StoreMemberRequest $request): MemberResource
    {
        $this->authorize('create', Member::class);

        $member = Member::create([
            ...$request->validated(),
            'tenant_id' => tenant('id'),
        ]);

        return new MemberResource($member);
    }

    /**
     * Retrieve member.
     *
     * Get the details of a specific member.
     */
    public function show(Member $member): MemberResource
    {
        $this->authorize('view', $member);

        return new MemberResource($member);
    }

    /**
     * Update member.
     *
     * Update the details of an existing member.
     */
    public function update(UpdateMemberRequest $request, Member $member): MemberResource
    {
        $this->authorize('update', $member);

        $member->update($request->validated());

        return new MemberResource($member->fresh());
    }

    /**
     * Delete member.
     *
     * Remove a church member from the system.
     */
    public function destroy(Member $member): JsonResponse
    {
        $this->authorize('delete', $member);

        $member->delete();

        return response()->json(null, 204);
    }
}
