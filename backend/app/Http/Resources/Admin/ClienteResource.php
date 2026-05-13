<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Resource pública do cliente — NUNCA expõe pfx_encrypted, pfx_senha_encrypted,
 * api_key_hash nem client_secret_hash.
 */
class ClienteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nome_empresa' => $this->nome_empresa,
            'cnpj' => $this->cnpj,
            'email' => $this->email,
            'telefone' => $this->telefone,

            'client_id' => $this->client_id,
            'api_key_preview' => $this->preview($this->api_key_hash, 'nfse'),

            // Cert (só metadata, nunca o PFX)
            'cert_validade' => $this->cert_validade?->format('Y-m-d'),
            'cert_cnpj' => $this->cert_cnpj,
            'tem_certificado' => ! empty($this->pfx_encrypted),

            // Endereço
            'cep' => $this->cep,
            'uf' => $this->uf,
            'codigo_municipio_ibge' => $this->codigo_municipio_ibge,
            'logradouro' => $this->logradouro,
            'numero' => $this->numero,
            'bairro' => $this->bairro,
            'complemento' => $this->complemento,

            // Tributário
            'inscricao_municipal' => $this->inscricao_municipal,
            'razao_social_prestador' => $this->razao_social_prestador,
            'regime_especial_tributacao' => (int) $this->regime_especial_tributacao,
            'simples_nacional' => (int) $this->simples_nacional,

            // Toggles
            'ambiente' => $this->ambiente,
            'incluir_ibscbs' => (bool) $this->incluir_ibscbs,
            'is_ativo' => (bool) $this->is_ativo,

            // Timestamps
            'data_criacao' => $this->data_criacao?->toIso8601String(),
            'data_alteracao' => $this->data_alteracao?->toIso8601String(),
        ];
    }

    /**
     * Como o api_key_hash é bcrypt opaco, geramos só um placeholder genérico.
     * Para mostrar realmente os 4 últimos da chave plana use o endpoint de
     * regerar (ele entrega a key UMA vez).
     */
    private function preview(?string $hash, string $prefixo): ?string
    {
        if (empty($hash)) {
            return null;
        }

        return $prefixo.'_***'.substr($hash, -4);
    }
}
