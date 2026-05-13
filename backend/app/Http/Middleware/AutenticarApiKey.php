<?php

namespace App\Http\Middleware;

use App\Models\Cliente;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

/**
 * Autentica requests da API multi-tenant via header `X-Api-Key`.
 *
 * - Lookup é por hash bcrypt → fazemos full-table scan dentro de um cache de 60s
 *   (com invalidação manual via `forget` em fluxos de revoke/rotate).
 * - Popula `$request->attributes->set('cliente', $cliente)` pra controllers acessarem.
 * - Aplica rate limit de 100 req/min por cliente.
 */
class AutenticarApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->header('X-Api-Key');

        if (empty($apiKey)) {
            return response()->json([
                'message' => 'Header X-Api-Key obrigatório.',
            ], 401);
        }

        $cliente = $this->resolverCliente($apiKey);

        if (! $cliente) {
            return response()->json([
                'message' => 'API key inválida ou cliente desativado.',
            ], 401);
        }

        // Rate limit por cliente: 100 req/min
        $limiterKey = 'nfse-api:cliente:'.$cliente->id;
        if (RateLimiter::tooManyAttempts($limiterKey, 100)) {
            $seconds = RateLimiter::availableIn($limiterKey);

            return response()->json([
                'message' => 'Muitas requisições. Aguarde alguns segundos.',
                'retry_after_seconds' => $seconds,
            ], 429)->header('Retry-After', (string) $seconds);
        }
        RateLimiter::hit($limiterKey, 60);

        $request->attributes->set('cliente', $cliente);

        return $next($request);
    }

    /**
     * Resolve o cliente a partir da API key plana. Cache curto evita full-scan
     * em todo request.
     */
    private function resolverCliente(string $apiKey): ?Cliente
    {
        // Cache: hash sha256(apiKey) → cliente_id resolvido
        $cacheKey = 'nfse-api:apikey:'.hash('sha256', $apiKey);

        $clienteId = Cache::remember($cacheKey, 60, function () use ($apiKey) {
            // Bcrypt não é searchable — full scan + check (para volume baixo de clientes).
            // Em escala maior usar HMAC-SHA256 do plain como índice.
            return Cliente::query()
                ->where('is_ativo', true)
                ->get(['id', 'api_key_hash'])
                ->first(fn (Cliente $c) => Hash::check($apiKey, $c->api_key_hash))
                ?->id;
        });

        if (! $clienteId) {
            Cache::forget($cacheKey);

            return null;
        }

        return Cliente::query()->where('is_ativo', true)->find($clienteId);
    }
}
