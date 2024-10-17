<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaginatedToolResource extends JsonResource
{
    public function toArray(Request $request) : array
    {
        return [
            ...parent::toArray($request),
            'data' => ToolResource::collection($this->resource->items()),
        ];
    }
}
