<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Cliente da API multi-tenant de NFS-e.
 *
 * Cada cliente representa um cartório/empresa que emite suas próprias NFS-es,
 * com cert A1, IM, ambiente e configuração tributária próprios. Auth via
 * `X-Api-Key` (verificada contra `api_key_hash`).
 *
 * Campos sensíveis (`pfx_encrypted`, `pfx_senha_encrypted`) são guardados via
 * `Crypt` do Laravel (chave em APP_KEY) — descriptografados no `CertificadoLoader`.
 */
class Cliente extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'cliente';

    const CREATED_AT = 'data_criacao';

    const UPDATED_AT = 'data_alteracao';

    const DELETED_AT = 'data_exclusao';

    protected $fillable = [
        'nome_empresa',
        'cnpj',
        'email',
        'telefone',
        'api_key_hash',
        'client_id',
        'client_secret_hash',
        'pfx_encrypted',
        'pfx_senha_encrypted',
        'cert_validade',
        'cert_cnpj',
        'inscricao_municipal',
        'razao_social_prestador',
        'codigo_municipio_ibge',
        'uf',
        'cep',
        'logradouro',
        'numero',
        'bairro',
        'complemento',
        'regime_especial_tributacao',
        'simples_nacional',
        'ambiente',
        'incluir_ibscbs',
        'is_ativo',
    ];

    protected $hidden = [
        'api_key_hash',
        'client_secret_hash',
        'pfx_encrypted',
        'pfx_senha_encrypted',
    ];

    protected function casts(): array
    {
        return [
            // Conteúdo cifrado em repouso (Laravel Crypt + APP_KEY).
            'pfx_encrypted' => 'encrypted',
            'pfx_senha_encrypted' => 'encrypted',
            'cert_validade' => 'date',
            'is_ativo' => 'boolean',
            'incluir_ibscbs' => 'boolean',
            'regime_especial_tributacao' => 'integer',
            'simples_nacional' => 'integer',
        ];
    }

    /**
     * NFS-es emitidas pelo cliente.
     */
    public function nfsesEmissoes(): HasMany
    {
        return $this->hasMany(NfseEmissao::class, 'cliente_id');
    }

    // ===== Helpers de credencial =====

    /**
     * Gera uma API key plana (entregue UMA vez ao cliente). O hash é guardado.
     */
    public static function gerarApiKey(): string
    {
        return 'nfse_'.Str::random(48);
    }

    /**
     * Gera client secret plano. Hash via bcrypt fica em `client_secret_hash`.
     */
    public static function gerarClientSecret(): string
    {
        return 'sk_'.Str::random(48);
    }

    public static function gerarClientId(): string
    {
        return 'cli_'.Str::lower(Str::random(28));
    }

    /**
     * Compara uma API key plana com o hash armazenado.
     */
    public function verificarApiKey(string $plain): bool
    {
        return Hash::check($plain, $this->api_key_hash);
    }

    public function verificarClientSecret(string $plain): bool
    {
        return Hash::check($plain, $this->client_secret_hash);
    }

    public function ehProducao(): bool
    {
        return $this->ambiente === 'producao';
    }
}
