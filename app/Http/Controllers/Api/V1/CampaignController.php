<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreCampaignRequest;
use App\Http\Requests\Api\V1\UpdateCampaignRequest;
use App\Http\Resources\CampaignCollection;
use App\Http\Resources\CampaignResource;
use App\Models\Campaign;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CampaignController extends Controller
{
    /**
     * List campaigns.
     *
     * Retrieve a paginated list of fundraising campaigns.
     *
     * @queryParam status string Filter by status (draft, active, completed, cancelled).
     * @queryParam fund_id int Filter by fund ID.
     * @queryParam ongoing boolean Filter for currently ongoing campaigns. Example: true
     * @queryParam per_page int Number of items per page. Example: 15
     */
    public function index(Request $request): CampaignCollection
    {
        $this->authorize('viewAny', Campaign::class);

        $query = Campaign::with('fund');

        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->has('fund_id')) {
            $query->where('fund_id', $request->input('fund_id'));
        }

        if ($request->boolean('ongoing', false)) {
            $query->ongoing();
        }

        $campaigns = $query
            ->orderBy('start_date', 'desc')
            ->paginate($request->input('per_page', 15));

        return new CampaignCollection($campaigns);
    }

    /**
     * Create campaign.
     *
     * Create a new fundraising campaign.
     */
    public function store(StoreCampaignRequest $request): CampaignResource
    {
        $this->authorize('create', Campaign::class);

        $campaign = Campaign::create([
            ...$request->validated(),
            'tenant_id' => tenant('id'),
        ]);

        return new CampaignResource($campaign->load('fund'));
    }

    /**
     * Retrieve campaign.
     *
     * Get details of a specific campaign including progress.
     */
    public function show(Campaign $campaign): CampaignResource
    {
        $this->authorize('view', $campaign);

        return new CampaignResource($campaign->load('fund'));
    }

    /**
     * Update campaign.
     *
     * Update an existing campaign's details.
     */
    public function update(UpdateCampaignRequest $request, Campaign $campaign): CampaignResource
    {
        $this->authorize('update', $campaign);

        $campaign->update($request->validated());

        return new CampaignResource($campaign->fresh()->load('fund'));
    }

    /**
     * Delete campaign.
     *
     * Remove a campaign from the system.
     */
    public function destroy(Campaign $campaign): JsonResponse
    {
        $this->authorize('delete', $campaign);

        $campaign->delete();

        return response()->json(null, 204);
    }
}
