<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\NfseEmissaoAdminResource;
use App\Models\Cliente;
use App\Models\NfseEmissao;
use App\Services\NFSe\NfseEmissorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Listagem/visualização admin das NFS-es emitidas (somente leitura).
 *
 * A emissão real continua sendo via /api/v1/nfse com X-Api-Key (B2B).
 * Aqui o admin só inspeciona/baixa o DANFSe.
 */
class NfseEmissaoController extends Controller
{
    /**
     * GET /api/admin/nfses
     *
     * Filtros: cliente_id, status, data_de, data_ate, chave (parcial).
     */
    public function index(Request $request): JsonResponse
    {
        $query = NfseEmissao::query()
            ->with('cliente:id,nome_empresa,cnpj')
            ->orderByDesc('data_criacao');

        if ($clienteId = $request->query('cliente_id')) {
            $query->where('cliente_id', $clienteId);
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($chave = trim((string) $request->query('chave'))) {
            $query->where('chave_acesso', 'like', "%{$chave}%");
        }

        if ($dataDe = $request->query('data_de')) {
            $query->whereDate('data_criacao', '>=', $dataDe);
        }

        if ($dataAte = $request->query('data_ate')) {
            $query->whereDate('data_criacao', '<=', $dataAte);
        }

        $perPage = (int) min(100, max(5, (int) $request->query('per_page', 20)));

        return NfseEmissaoAdminResource::collection($query->paginate($perPage))->response();
    }

    /**
     * GET /api/admin/nfses/{nfse}
     *
     * Inclui detalhe (descriptografa tomador / discriminação / payload) — passa
     * `?detalhe=1` no resource pra exibir tudo.
     */
    public function show(Request $request, NfseEmissao $nfse): JsonResponse
    {
        $nfse->load('cliente:id,nome_empresa,cnpj');
        $request->merge(['detalhe' => true]);

        return (new NfseEmissaoAdminResource($nfse))->response();
    }

    /**
     * GET /api/admin/nfses/{nfse}/pdf
     *
     * Gera DANFSe via SDK (mesmo que o endpoint público /api/v1/danfse).
     */
    public function baixarPdf(NfseEmissao $nfse): Response
    {
        if (empty($nfse->chave_acesso)) {
            return response('NFS-e ainda não tem chave de acesso (não emitida).', 404);
        }

        /** @var Cliente $cliente */
        $cliente = $nfse->cliente;
        $service = new NfseEmissorService($cliente);

        try {
            $pdf = $service->gerarDanfsePdf($nfse->chave_acesso);
        } catch (\Throwable $e) {
            return response('Erro ao gerar PDF: '.$e->getMessage(), 500);
        }

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="danfse-'.$nfse->chave_acesso.'.pdf"',
        ]);
    }
}
