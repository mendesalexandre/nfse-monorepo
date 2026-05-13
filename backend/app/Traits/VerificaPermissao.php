<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait VerificaPermissao
{
    protected function verificarPermissao(string $permissao): ?JsonResponse
    {
        $user = auth()->user();

        if (! $user) {
            return response()->json([
                'message' => 'Não autenticado.',
            ], 401);
        }

        if ($user->temPermissao($permissao)) {
            return null;
        }

        return response()->json([
            'message' => 'Você não tem permissão para acessar este recurso.',
            'permissoes_necessarias' => [$permissao],
        ], 403);
    }

    protected function verificarAlgumaPermissao(array $permissoes): ?JsonResponse
    {
        $user = auth()->user();

        if (! $user) {
            return response()->json([
                'message' => 'Não autenticado.',
            ], 401);
        }

        if ($user->temAlgumaPermissao($permissoes)) {
            return null;
        }

        return response()->json([
            'message' => 'Você não tem permissão para acessar este recurso.',
            'permissoes_necessarias' => $permissoes,
        ], 403);
    }
}
