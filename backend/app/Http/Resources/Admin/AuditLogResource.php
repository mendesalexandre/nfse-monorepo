<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuditLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'cliente_id' => $this->cliente_id,
            'cliente_nome' => $this->whenLoaded('cliente', fn () => $this->cliente?->nome_empresa),
            'acao' => $this->acao,
            'recurso_tipo' => $this->recurso_tipo,
            'recurso_id' => $this->recurso_id,
            'ip_origem' => $this->ip_origem,
            'user_agent' => $this->user_agent,
            'dados_request' => $this->dados_request ? json_decode($this->dados_request, true) : null,
            'dados_response' => $this->dados_response ? json_decode($this->dados_response, true) : null,
            'data_criacao' => $this->data_criacao?->toIso8601String(),
        ];
    }
}
