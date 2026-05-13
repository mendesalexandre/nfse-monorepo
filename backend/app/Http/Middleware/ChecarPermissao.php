<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ChecarPermissao
{
    public function handle(Request $request, Closure $next, string ...$permissoes): Response
    {
        $user = auth()->user();

        if (! $user) {
            return response()->json([
                'message' => 'Não autenticado.',
            ], 401);
        }

        // Admin bypass: grupo "Administrador" tem acesso total
        if ($user->isAdmin()) {
            return $next($request);
        }

        // Verificar se tem ALGUMA das permissões (lógica OR)
        foreach ($permissoes as $permissao) {
            if ($user->temPermissao($permissao)) {
                return $next($request);
            }
        }

        return response()->json([
            'message' => 'Você não tem permissão para acessar este recurso.',
            'permissoes_necessarias' => $permissoes,
        ], 403);
    }
}
