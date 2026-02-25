<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\ApiTokenResource;
use App\Models\ApiToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TokenManagementController extends BaseApiController
{
    /**
     * Listar todos os tokens.
     */
    public function index(Request $request): JsonResponse
    {
        $query = ApiToken::with('createdBy');

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $sortBy = $request->input('sort_by', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');
        $query->orderBy($sortBy, $sortDirection);

        $perPage = $request->input('per_page', 20);
        $perPage = min(max((int) $perPage, 1), 100);

        $tokens = $query->paginate($perPage);

        return $this->paginatedResponse($tokens, ApiTokenResource::class);
    }

    /**
     * Criar novo token.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'expires_in_days' => 'nullable|integer|min:1|max:365',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|in:read,write,sync,ai,admin',
        ]);

        try {
            $apiToken = $request->attributes->get('api_token');
            $userId = $apiToken?->created_by_id;

            $token = ApiToken::create([
                'name' => $validated['name'] ?? 'API Token ' . now()->format('Y-m-d H:i'),
                'token' => ApiToken::generateToken(),
                'created_by_id' => $userId,
                'expires_at' => isset($validated['expires_in_days']) 
                    ? now()->addDays($validated['expires_in_days']) 
                    : null,
                'permissions' => $validated['permissions'] ?? ['*'],
                'is_active' => true,
            ]);

            // Retornar o token completo apenas uma vez na criação
            return $this->successResponse(
                new ApiTokenResource($token),
                'Token criado com sucesso. Guarde o token, pois ele não será mostrado novamente.',
                201
            );
        } catch (\Exception $e) {
            Log::error('Erro ao criar token: ' . $e->getMessage());
            return $this->errorResponse('Erro ao criar token: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Exibir um token específico.
     */
    public function show(ApiToken $apiToken): JsonResponse
    {
        return $this->successResponse(new ApiTokenResource($apiToken->load('createdBy')));
    }

    /**
     * Atualizar um token.
     */
    public function update(Request $request, ApiToken $apiToken): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|in:read,write,sync,ai,admin',
        ]);

        try {
            $updateData = [];

            if (isset($validated['name'])) {
                $updateData['name'] = $validated['name'];
            }

            if (isset($validated['is_active'])) {
                $updateData['is_active'] = $validated['is_active'];
            }

            if (isset($validated['permissions'])) {
                $updateData['permissions'] = $validated['permissions'];
            }

            $apiToken->update($updateData);

            return $this->successResponse(
                new ApiTokenResource($apiToken->fresh()->load('createdBy')),
                'Token atualizado com sucesso.'
            );
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar token: ' . $e->getMessage());
            return $this->errorResponse('Erro ao atualizar token: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Revogar um token.
     */
    public function destroy(ApiToken $apiToken): JsonResponse
    {
        try {
            $apiToken->update(['is_active' => false]);

            return $this->successResponse(null, 'Token revogado com sucesso.');
        } catch (\Exception $e) {
            Log::error('Erro ao revogar token: ' . $e->getMessage());
            return $this->errorResponse('Erro ao revogar token: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Obter informações do token atual.
     */
    public function me(Request $request): JsonResponse
    {
        $apiToken = $request->attributes->get('api_token');

        return $this->successResponse([
            'token' => new ApiTokenResource($apiToken->load('createdBy')),
            'permissions' => $apiToken->permissions ?? ['*'],
        ]);
    }
}
