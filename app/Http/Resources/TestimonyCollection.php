<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class TestimonyCollection extends ResourceCollection
{
    public $collects = TestimonyResource::class;

    public function toArray(Request $request): array
    {
        return parent::toArray($request);
    }
}
