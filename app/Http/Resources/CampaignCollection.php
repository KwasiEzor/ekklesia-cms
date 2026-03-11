<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class CampaignCollection extends ResourceCollection
{
    public $collects = CampaignResource::class;

    public function toArray(Request $request): array
    {
        return parent::toArray($request);
    }
}
