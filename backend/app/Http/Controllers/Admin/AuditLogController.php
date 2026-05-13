<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\AuditLogResource;
use App\Models\AuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    /**
     * GET /api/admin/audit-logs
     *
     * Filtros: cliente_id, acao, recurso_tipo, data_de/data_ate.
     */
    public function index(Request $request): JsonResponse
    {
        $query = AuditLog::query()
            ->with('cliente:id,nome_empresa')
            ->orderByDesc('data_criacao');

        if ($clienteId = $request->query('cliente_id')) {
            $query->where('cliente_id', $clienteId);
        }

        if ($acao = $request->query('acao')) {
            $query->where('acao', $acao);
        }

        if ($recurso = $request->query('recurso_tipo')) {
            $query->where('recurso_tipo', $recurso);
        }

        if ($dataDe = $request->query('data_de')) {
            $query->whereDate('data_criacao', '>=', $dataDe);
        }

        if ($dataAte = $request->query('data_ate')) {
            $query->whereDate('data_criacao', '<=', $dataAte);
        }

        $perPage = (int) min(100, max(5, (int) $request->query('per_page', 20)));

        return AuditLogResource::collection($query->paginate($perPage))->response();
    }
}
