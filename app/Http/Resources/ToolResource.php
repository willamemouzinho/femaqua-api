<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="ToolResource",
 *     type="object",
 *     title="Tool Resource",
 *     description="Detalhes da ferramenta",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="title", type="string", example="Laravel"),
 *     @OA\Property(property="link", type="string", format="url", example="https://laravel.com"),
 *     @OA\Property(property="description", type="string", example="Framework PHP para aplicações web"),
 *     @OA\Property(
 *         property="tags",
 *         type="array",
 *         @OA\Items(
 *             type="string",
 *             example="PHP"
 *         )
 *     ),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2023-01-01T00:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2023-01-02T00:00:00Z"),
 * )
 */
class ToolResource extends JsonResource
{
    public function toArray(Request $request) : array
    {
        return [
            "id" => $this->id,
            "title" => $this->title,
            "link" => $this->link,
            "description" => $this->description,
            "tags" => $this->whenLoaded('tags', fn () => $this->tags->pluck('name')->toArray()),
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at,
        ];
    }
}
