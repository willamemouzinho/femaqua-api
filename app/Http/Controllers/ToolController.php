<?php

namespace App\Http\Controllers;

use App\Http\Resources\PaginatedToolResource;
use App\Http\Resources\ToolResource;
use App\Models\Tool;
use App\Http\Requests\StoreToolRequest;
use App\Http\Requests\UpdateToolRequest;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(
 *     name="tools",
 *     description="Endpoints para gerenciamento de ferramentas"
 * )
 */
class ToolController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/tools",
     *     tags={"tools"},
     *     summary="Listar ferramentas",
     *     operationId="getTools",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Número da página para paginação",
     *         required=false,
     *         @OA\Schema(type="string", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="tag",
     *         in="query",
     *         description="Filtrar ferramentas por tag",
     *         required=false,
     *         @OA\Schema(type="string", example="PHP")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de ferramentas paginada",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="current_page",
     *                 type="integer",
     *                 example=1
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/ToolResource")
     *             ),
     *             @OA\Property(
     *                 property="first_page_url",
     *                 type="string",
     *                 example="http://femaqua-api.test/api/tools?page=1"
     *             ),
     *             @OA\Property(
     *                 property="from",
     *                 type="integer",
     *                 example=1
     *             ),
     *             @OA\Property(
     *                 property="next_page_url",
     *                 type="string",
     *                 nullable=true,
     *                 example=null
     *             ),
     *             @OA\Property(
     *                 property="path",
     *                 type="string",
     *                 example="http://femaqua-api.test/api/tools"
     *             ),
     *             @OA\Property(
     *                 property="per_page",
     *                 type="integer",
     *                 example=10
     *             ),
     *             @OA\Property(
     *                 property="prev_page_url",
     *                 type="string",
     *                 nullable=true,
     *                 example=null
     *             ),
     *             @OA\Property(
     *                 property="to",
     *                 type="integer",
     *                 example=4
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erro de validação",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Não autenticado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
    public function index(Request $request) : JsonResponse
    {
        $validated_data = $request->validate([
            'page' => 'nullable|integer',
            'tag' => 'nullable|string|max:255',
        ]);
        $tag_filter = $validated_data['tag'] ?? null;

        $tools = Tool::
            where('user_id', $request->user()->id)
            ->when($tag_filter, function (Builder $query) use ($tag_filter) {
                return $query->whereHas('tags', function (Builder $query) use ($tag_filter) {
                    $query->where('name', $tag_filter);
                });
            })
            ->with('tags')
            ->orderBy('created_at', 'desc')
            ->simplePaginate(10);

        return response()->json(new PaginatedToolResource($tools), 200);
    }

    /**
     * @OA\Get(
     *     path="/api/tools/{id}",
     *     tags={"tools"},
     *     summary="Obter uma ferramenta específica",
     *     operationId="getToolById",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID da ferramenta",
     *         required=true,
     *         @OA\Schema(type="string", example="1")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Detalhes da ferramenta",
     *         @OA\JsonContent(ref="#/components/schemas/ToolResource")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erro de validação",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Ferramenta não encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Tool not found.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Permissão negada",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="You do not own this tool.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Não autenticado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
    public function show(string $tool_id) : JsonResponse
    {
        $validator = Validator::make(['tool_id' => $tool_id], [
            'tool_id' => 'integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $tool = Tool::
            where('id', $tool_id)
            ->with('tags')
            ->first();

        if (! $tool) {
            return response()->json(['message' => 'Tool not found.'], 404);
        }

        if (! Gate::allows('view', $tool)) {
            return response()->json(['message' => 'You do not own this tool.'], 403);
        }

        return response()->json(new ToolResource($tool), 200);
    }

    /**
     * @OA\Post(
     *     path="/api/tools",
     *     tags={"tools"},
     *     summary="Criar uma nova ferramenta",
     *     operationId="createTool",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title","link","description","tags"},
     *             @OA\Property(property="title", type="string", example="Laravel"),
     *             @OA\Property(property="link", type="string", format="url", example="https://laravel.com"),
     *             @OA\Property(property="description", type="string", example="Framework PHP para aplicações web"),
     *             @OA\Property(
     *                 property="tags",
     *                 type="array",
     *                 @OA\Items(type="string", example="PHP")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Ferramenta criada com sucesso",
     *         @OA\JsonContent(ref="#/components/schemas/ToolResource")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erro de validação",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Não autenticado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
    public function store(StoreToolRequest $request) : JsonResponse
    {
        $tool_data = $request->validated();
        $tool = Tool::create([
            'title' => $tool_data['title'],
            'link' => $tool_data['link'],
            'description' => $tool_data['description'],
            'user_id' => $request->user()->id
        ]);

        $tag_ids = [];
        foreach ($tool_data['tags'] as $tag_name) {
            $tag = Tag::firstOrCreate(['name' => strtolower($tag_name)]);
            $tag_ids[] = $tag->id;
        }

        $tool->tags()->sync($tag_ids);
        $tool->load('tags');

        return response()->json(new ToolResource($tool), 201);
    }

    /**
     * @OA\Put(
     *     path="/api/tools/{id}",
     *     tags={"tools"},
     *     summary="Atualizar uma ferramenta existente",
     *     operationId="updateTool",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID da ferramenta",
     *         required=true,
     *         @OA\Schema(type="string", example="1")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title","link","description","tags"},
     *             @OA\Property(property="title", type="string", example="Laravel"),
     *             @OA\Property(property="link", type="string", format="url", example="https://laravel.com"),
     *             @OA\Property(property="description", type="string", example="Framework PHP atualizado"),
     *             @OA\Property(
     *                 property="tags",
     *                 type="array",
     *                 @OA\Items(type="string", example="PHP")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Ferramenta atualizada com sucesso",
     *         @OA\JsonContent(ref="#/components/schemas/ToolResource")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Permissão negada",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="You do not own this tool.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Ferramenta não encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Tool not found.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erro de validação",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Não autenticado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
    public function update(UpdateToolRequest $request, string $tool_id) : JsonResponse
    {
        $validator = Validator::make(['tool_id' => $tool_id], [
            'tool_id' => 'integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $tool = Tool::
            where('id', $tool_id)
            ->with('tags')
            ->first();

        if (! $tool) {
            return response()->json(['message' => 'Tool not found.'], 404);
        }

        if (! Gate::allows('update', $tool)) {
            return response()->json(['message' => 'You do not own this tool.'], 403);
        }

        $tool_data = $request->validated();
        $tool->update([
            'title' => $tool_data['title'],
            'link' => $tool_data['link'],
            'description' => $tool_data['description'],
        ]);

        if (($tool_data['tags'])) {
            $tag_ids = [];
            foreach ($tool_data['tags'] as $tag_name) {
                $tag = Tag::firstOrCreate(['name' => strtolower($tag_name)]);
                $tag_ids[] = $tag->id;
            }
            $tool->tags()->sync($tag_ids);
        }

        $tool->load('tags');

        return response()->json(new ToolResource($tool), 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/tools/{id}",
     *     tags={"tools"},
     *     summary="Excluir uma ferramenta",
     *     operationId="deleteTool",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID da ferramenta",
     *         required=true,
     *         @OA\Schema(type="string", example="1")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Ferramenta excluída com sucesso",
     *         @OA\MediaType(
     *             mediaType="application/json"
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erro de validação",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Ferramenta não encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Tool not found.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Permissão negada",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="You do not own this tool.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Não autenticado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
    public function destroy(string $tool_id) : JsonResponse
    {
        $validator = Validator::make(['tool_id' => $tool_id], [
            'tool_id' => 'integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $tool = Tool::where('id', $tool_id)->first();

        if (! $tool) {
            return response()->json(['message' => 'Tool not found.'], 404);
        }

        if (! Gate::allows('delete', $tool)) {
            return response()->json(['message' => 'You do not own this tool.'], 403);
        }

        $tool->delete();

        return response()->json([], 204);
    }
}
