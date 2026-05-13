<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Log append-only de toda operação fiscal/sensível (LGPD).
 *
 * Não tem `data_alteracao` (timestamps `false`). Não é editável depois de
 * criado — quem usa só insere. Pra purga, use uma política de retenção
 * separada (job que apaga > N dias).
 */
class AuditLog extends Model
{
    protected $table = 'audit_log';

    // Append-only — só queremos data_criacao
    public $timestamps = false;

    protected $fillable = [
        'cliente_id',
        'acao',
        'recurso_tipo',
        'recurso_id',
        'ip_origem',
        'user_agent',
        'dados_request',
        'dados_response',
        'data_criacao',
    ];

    protected function casts(): array
    {
        return [
            'data_criacao' => 'datetime',
        ];
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }
}
