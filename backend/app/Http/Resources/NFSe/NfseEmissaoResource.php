<?php

namespace App\Http\Resources\NFSe;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Resource pública da NFS-e emitida.
 *
 * NÃO expõe `tomador_*_encrypted`, `discriminacao_encrypted`, `request_payload`
 * nem `response_xml_encrypted`. Apenas o que o cliente da API precisa pra
 * tracking.
 */
class NfseEmissaoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
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
    }
}
