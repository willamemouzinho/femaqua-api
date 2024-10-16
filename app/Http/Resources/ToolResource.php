<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ToolResource extends JsonResource
{
    public function toArray(Request $request) : array
    {
        return [
            "id" => $this->id,
            "title" => $this->title,
            "link" => $this->link,
            "description" => $this->description,
            // "tags" => $this->tags,
            "tags" => $this->whenLoaded('tags', fn () => $this->tags->pluck('name')->toArray()),
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at,
        ];
    }
}
