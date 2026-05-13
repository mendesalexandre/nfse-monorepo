<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Registro local de cada DPS emitida pelo cliente, com a resposta do SEFIN.
 *
 * Campos sensíveis (documento/nome do tomador, discriminação, payload e
 * resposta) são guardados criptografados (Crypt + APP_KEY).
 *
 * Status interno:
 *  - pendente     → DPS montada, ainda não enviada
 *  - emitida      → SEFIN retornou cStat=100 e chave de acesso
 *  - rejeitada    → SEFIN retornou cStat de rejeição (não emitida)
 *  - cancelada    → cancelada via evento 101101
 *  - substituida  → cancelada por substituição (105102)
 *  - erro         → falha de transporte / cert / OPENSSL etc.
 */
class NfseEmissao extends Model
{
    use HasFactory;

    protected $table = 'nfse_emissao';

    const CREATED_AT = 'data_criacao';

    const UPDATED_AT = 'data_alteracao';

    protected $fillable = [
        'cliente_id',
        'chave_acesso',
        'numero_nfse',
        'c_stat',
        'x_motivo',
        'numero_dps',
        'serie_dps',
        'tomador_documento_encrypted',
        'tomador_nome_encrypted',
        'valor_servicos',
        'valor_iss',
        'discriminacao_encrypted',
        'request_payload',
        'response_xml_encrypted',
        'data_emissao',
        'data_processamento',
        'status',
    ];

    protected $hidden = [
        'tomador_documento_encrypted',
        'tomador_nome_encrypted',
        'discriminacao_encrypted',
        'request_payload',
        'response_xml_encrypted',
    ];

    protected function casts(): array
    {
        return [
            // Criptografados em repouso
            'tomador_documento_encrypted' => 'encrypted',
            'tomador_nome_encrypted' => 'encrypted',
            'discriminacao_encrypted' => 'encrypted',
            'request_payload' => 'encrypted:array',
            'response_xml_encrypted' => 'encrypted',

            // Valores monetários como float (não decimal:X que vira string)
            'valor_servicos' => 'float',
            'valor_iss' => 'float',

            'numero_dps' => 'integer',
            'c_stat' => 'integer',

            'data_emissao' => 'datetime',
            'data_processamento' => 'datetime',
        ];
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    // ===== Helpers de status =====

    public function ehEmitida(): bool
    {
        return $this->status === 'emitida';
    }

    public function ehCancelavel(): bool
    {
        return in_array($this->status, ['emitida'], true);
    }
}
