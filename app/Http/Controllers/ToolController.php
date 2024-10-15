<?php

namespace App\Http\Controllers;

use App\Http\Resources\ToolResource;
use App\Models\Tool;
use App\Http\Requests\StoreToolRequest;
use App\Http\Requests\UpdateToolRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ToolController extends Controller
{
    public function index(Request $request) : JsonResponse
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

    public function store(StoreToolRequest $request) : JsonResponse
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

    public function show(Request $request, string $toolId) : JsonResponse
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

    public function update(UpdateToolRequest $request, string $toolId) : JsonResponse
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

    public function destroy(string $toolId) : JsonResponse
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
}
