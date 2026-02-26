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
    public function index(Request $request): MemberCollection
    {
        $query = Member::query();

        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->has('cell_group_id')) {
            $query->where('cell_group_id', $request->input('cell_group_id'));
        }

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
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

    public function store(StoreMemberRequest $request): MemberResource
    {
        $member = Member::create([
            ...$request->validated(),
            'tenant_id' => tenant('id'),
        ]);

        return new MemberResource($member);
    }

    public function show(Member $member): MemberResource
    {
        return new MemberResource($member);
    }

    public function update(UpdateMemberRequest $request, Member $member): MemberResource
    {
        $member->update($request->validated());

        return new MemberResource($member->fresh());
    }

    public function destroy(Member $member): JsonResponse
    {
        $member->delete();

        return response()->json(null, 204);
    }
}
