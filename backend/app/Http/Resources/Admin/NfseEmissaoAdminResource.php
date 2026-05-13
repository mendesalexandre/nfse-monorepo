<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Resource admin da NFS-e — pode descriptografar tomador/discriminação pra
 * exibição (já que é só o painel autenticado por sessão admin que acessa).
 */
class NfseEmissaoAdminResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $detalhe = (bool) $request->boolean('detalhe', false);

        $base = [
            'id' => $this->id,
            'cliente_id' => $this->cliente_id,
            'cliente_nome' => $this->whenLoaded('cliente', fn () => $this->cliente->nome_empresa),

            'chave_acesso' => $this->chave_acesso,
            'numero_nfse' => $this->numero_nfse,
            'numero_dps' => $this->numero_dps,
            'serie_dps' => $this->serie_dps,

            'c_stat' => $this->c_stat,
            'x_motivo' => $this->x_motivo,
            'status' => $this->status,

            'valor_servicos' => (float) $this->valor_servicos,
            'valor_iss' => (float) $this->valor_iss,

            'data_emissao' => $this->data_emissao?->toIso8601String(),
            'data_processamento' => $this->data_processamento?->toIso8601String(),
            'data_criacao' => $this->data_criacao?->toIso8601String(),
        ];

        if ($detalhe) {
            $base['tomador_documento'] = $this->tomador_documento_encrypted;
            $base['tomador_nome'] = $this->tomador_nome_encrypted;
            $base['discriminacao'] = $this->discriminacao_encrypted;
            $base['request_payload'] = $this->request_payload;
            $base['response_xml'] = $this->response_xml_encrypted;
        }

        return $base;
    }
}
