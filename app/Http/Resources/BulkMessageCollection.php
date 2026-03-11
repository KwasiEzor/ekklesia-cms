<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class BulkMessageCollection extends ResourceCollection
{
    public $collects = BulkMessageResource::class;

    public function toArray(Request $request): array
    {
        return parent::toArray($request);
    }
}
