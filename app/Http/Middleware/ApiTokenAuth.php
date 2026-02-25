<?php

namespace App\Http\Middleware;

use App\Models\ApiToken;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiTokenAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission = null): Response
    {
        // Obter token do header
        $token = $request->header('X-API-Token');
        
        // Também permitir via query string (menos seguro, mas útil para testes)
        if (!$token) {
            $token = $request->query('api_token');
        }
        
        // Ou via Bearer token
        if (!$token && $request->header('Authorization')) {
            $authHeader = $request->header('Authorization');
            if (str_starts_with($authHeader, 'Bearer ')) {
                $token = substr($authHeader, 7);
            }
        }

        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'API token não fornecido. Use o header X-API-Token ou Authorization: Bearer.',
            ], 401);
        }

        // Buscar e validar token
        $apiToken = ApiToken::findValid($token);

        if (!$apiToken) {
            return response()->json([
                'success' => false,
                'message' => 'API token inválido ou expirado.',
            ], 401);
        }

        // Verificar permissão específica
        if ($permission && !$apiToken->hasPermission($permission)) {
            return response()->json([
                'success' => false,
                'message' => 'Token não possui permissão para esta ação.',
                'permission_required' => $permission,
            ], 403);
        }

        // Registrar uso
        $apiToken->recordUsage();

        // Adicionar informações do token à requisição
        $request->attributes->set('api_token', $apiToken);
        $request->attributes->set('api_user_id', $apiToken->created_by_id);

        return $next($request);
    }
}
