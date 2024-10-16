<?php

namespace App\Http\Controllers;

use App\Http\Resources\ToolResource;
use App\Models\Tool;
use App\Http\Requests\StoreToolRequest;
use App\Http\Requests\UpdateToolRequest;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class ToolController extends Controller
{
    public function indexOK(Request $request) : JsonResponse
    {
        $request->validate([
            'page' => 'nullable|integer',
            'tag' => 'nullable|string|max:255',
        ]);
        $tag = $request->query('tag');

        $tools = Tool::select(
            'id',
            'title',
            'link',
            'description',
            'tags',
            'created_at',
            'updated_at',
        )
            ->where('user_id', $request->user()->id)
            ->when($tag, function (Builder $query, string $tag) {
                $query->whereJsonContains('tags', $tag);
            })
            ->orderBy('updated_at', 'desc')->paginate(10);


        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'Tools retrieved successfully.',
            ],
            'data' => ['tools' => $tools],
        ], 200);
    }

    public function storeOK(StoreToolRequest $request) : JsonResponse
    {
        $toolData = $request->validated();
        $tool = Tool::create([...$toolData, 'user_id' => $request->user()->id]);

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'Tool created successfully.',
            ],
            'data' => new ToolResource($tool),
        ], 201);
    }

    public function showOK(Request $request, string $toolId) : JsonResponse
    {
        $tool = Tool::select(
            'id',
            'title',
            'link',
            'description',
            'tags',
            'created_at',
            'updated_at',
            'user_id',
        )->where('id', $toolId)->first();

        if (! $tool) {
            return response()->json([
                'meta' => [
                    'status' => 'error',
                    'message' => 'Tool not found.',
                ],
            ], 404);
        }

        if (! Gate::allows('view', $tool)) {
            return response()->json([
                'meta' => [
                    'status' => 'error',
                    'message' => 'You do not own this tool.',
                ],
            ], 403);
        }

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'Tool retrieved successfully.',
            ],
            'data' => [
                'tool' => new ToolResource($tool),
            ],
        ], 200);
    }

    public function updateOK(UpdateToolRequest $request, string $toolId) : JsonResponse
    {
        $tool = Tool::select(
            'id',
            'title',
            'link',
            'description',
            'tags',
            'created_at',
            'updated_at',
            'user_id',
        )->where('id', $toolId)->first();

        if (! $tool) {
            return response()->json([
                'meta' => [
                    'status' => 'error',
                    'message' => 'Tool not found.',
                ],
            ], 404);
        }

        if (! Gate::allows('update', $tool)) {
            return response()->json([
                'meta' => [
                    'status' => 'error',
                    'message' => 'You do not own this tool.',
                ],
            ], 403);
        }

        $toolData = $request->validated();
        $tool->update($toolData);

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'Tool updated successfully.',
            ],
            'data' => [
                'tool' => new ToolResource($tool),
            ],
        ], 200);
    }

    public function destroyOK(string $toolId) : JsonResponse
    {
        $tool = Tool::select('user_id')->where('id', $toolId)->first();

        if (! $tool) {
            return response()->json([
                'meta' => [
                    'status' => 'error',
                    'message' => 'Tool not found.',
                ],
            ], 404);
        }

        if (! Gate::allows('delete', $tool)) {
            return response()->json([
                'meta' => [
                    'status' => 'error',
                    'message' => 'You do not own this tool.',
                ],
            ], 403);
        }

        $tool->delete();

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'Tool deleted successfully.',
            ],
        ], 200);
    }

    // NEWS

    public function index(Request $request) : JsonResponse
    {
        $request->validate([
            'page' => 'nullable|integer',
            'tag' => 'nullable|string|max:255',
        ]);
        $tagFilter = $request->query('tag');

        if ($tagFilter) {
            $tools = Tool::
                where('user_id', $request->user()->id)
                ->whereHas('tags', function (Builder $query) use ($tagFilter) {
                    $query->where('name', $tagFilter);
                })
                ->with('tags')
                ->paginate();
        } else {
            $tools = Tool::
                where('user_id', $request->user()->id)
                ->with('tags')
                ->paginate();
        }

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'Tools retrieved successfully.',
            ],
            'data' => [
                'tools' => ToolResource::collection($tools)
            ],
        ], 200);
    }

    public function show(Request $request, string $toolId) : JsonResponse
    {
        $tool = Tool::
            where('id', $toolId)
            ->with('tags')
            ->first();


        if (! $tool) {
            return response()->json([
                'meta' => [
                    'status' => 'error',
                    'message' => 'Tool not found.',
                ],
            ], 404);
        }

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'Tool retrieved successfully.',
            ],
            'data' => [
                'tool' => new ToolResource($tool),
            ],
        ], 200);
    }

    public function update(UpdateToolRequest $request, string $toolId) : JsonResponse
    {
        $tool = Tool::
            where('id', $toolId)
            ->with('tags')
            ->first();

        if (! $tool) {
            return response()->json([
                'meta' => [
                    'status' => 'error',
                    'message' => 'Tool not found.',
                ],
            ], 404);
        }

        if (! Gate::allows('update', $tool)) {
            return response()->json([
                'meta' => [
                    'status' => 'error',
                    'message' => 'You do not own this tool.',
                ],
            ], 403);
        }

        $toolData = $request->validated();
        $tool->update([
            'title' => $toolData['title'],
            'link' => $toolData['link'],
            'description' => $toolData['description'],
        ]);

        // if (count($toolData['tags']) > 0) {
        //     foreach ($toolData['tags'] as $tag) {
        //         $hasTag = Tag::where('name', $tag)->first();
        //         if (! $hasTag) {
        //             $newTag = Tag::create(['name' => $tag]);
        //             $tool->tags()->attach($newTag->id);
        //         } else {
        //             $tool->tags()->attach($hasTag->id);
        //         }
        //     }
        // }

        if (($toolData['tags'])) {
            $tagIds = [];
            foreach ($toolData['tags'] as $tagName) {
                $tag = Tag::firstOrCreate(['name' => $tagName]);
                $tagIds[] = $tag->id;
            }

            // Atualizar a relação de tags
            $tool->tags()->sync($tagIds);
        }

        // Carregar as tags relacionadas
        $tool->load('tags');

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'Tool updated successfully.',
            ],
            'data' => [
                'tool' => new ToolResource($tool),
            ],
        ], 200);
    }

    public function destroy(string $toolId) : JsonResponse
    {
        $tool = Tool::where('id', $toolId)->first();

        if (! $tool) {
            return response()->json([
                'meta' => [
                    'status' => 'error',
                    'message' => 'Tool not found.',
                ],
            ], 404);
        }

        if (! Gate::allows('delete', $tool)) {
            return response()->json([
                'meta' => [
                    'status' => 'error',
                    'message' => 'You do not own this tool.',
                ],
            ], 403);
        }

        $tool->delete();

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'Tool deleted successfully.',
            ],
        ], 200);
    }

    public function store(StoreToolRequest $request) : JsonResponse
    {
        $toolData = $request->validated();
        $tool = Tool::create([
            'title' => $toolData['title'],
            'link' => $toolData['link'],
            'description' => $toolData['description'],
            'user_id' => $request->user()->id
        ]);

        $tagIds = [];
        foreach ($toolData['tags'] as $tagName) {
            $tag = Tag::firstOrCreate(['name' => $tagName]);
            $tagIds[] = $tag->id;
        }

        // Associar as tags à tool
        $tool->tags()->sync($tagIds);

        // Carregar as tags relacionadas
        $tool->load('tags');

        return response()->json(new ToolResource($tool), 201);

        // if (count($toolData['tags']) > 0) {
        //     foreach ($toolData['tags'] as $tag) {
        //         $hasTag = Tag::where('name', $tag)->first();
        //         if (! $hasTag) {
        //             $newTag = Tag::create(['name' => $tag]);
        //             $tool->tags()->attach($newTag->id);
        //         } else {
        //             $tool->tags()->attach($hasTag->id);
        //         }
        //     }
        // }

        // $tool->load('tags');
        // $tool->tags = $tool->tags->pluck('name')->toArray();

        // return response()->json([
        //     'meta' => [
        //         'status' => 'success',
        //         'message' => 'Tool created successfully.',
        //     ],
        //     'data' => new ToolResource($tool),
        // ], 201);
    }
}
