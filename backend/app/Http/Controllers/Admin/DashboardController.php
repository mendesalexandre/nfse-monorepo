<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Cliente;
use App\Models\NfseEmissao;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    /**
     * GET /api/admin/dashboard
     *
     * Cards agregados pro topo do painel.
     */
    public function index(): JsonResponse
    {
        $hoje = Carbon::today();
        $ultimas24h = Carbon::now()->subDay();
        $semanaPassada = Carbon::now()->subDays(7);

        return response()->json([
            'clientes' => [
                'total' => Cliente::count(),
                'ativos' => Cliente::where('is_ativo', true)->count(),
                'inativos' => Cliente::where('is_ativo', false)->count(),
                'cert_expirando_30d' => Cliente::whereDate('cert_validade', '<=', $hoje->copy()->addDays(30))
                    ->whereDate('cert_validade', '>=', $hoje)
                    ->count(),
                'cert_vencido' => Cliente::whereDate('cert_validade', '<', $hoje)->count(),
            ],
            'emissoes' => [
                'total' => NfseEmissao::count(),
                'ultimas_24h' => NfseEmissao::where('data_criacao', '>=', $ultimas24h)->count(),
                'ultimos_7d' => NfseEmissao::where('data_criacao', '>=', $semanaPassada)->count(),
                'emitidas' => NfseEmissao::where('status', 'emitida')->count(),
                'rejeitadas' => NfseEmissao::where('status', 'rejeitada')->count(),
                'canceladas' => NfseEmissao::where('status', 'cancelada')->count(),
                'erro' => NfseEmissao::where('status', 'erro')->count(),
                'pendente' => NfseEmissao::where('status', 'pendente')->count(),
            ],
            'audit' => [
                'total' => AuditLog::count(),
                'ultimas_24h' => AuditLog::where('data_criacao', '>=', $ultimas24h)->count(),
            ],
        ]);
    }
}
