<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Permissao extends Model
{
    use SoftDeletes;

    protected $table = 'permissao';

    const CREATED_AT = 'data_cadastro';

    const UPDATED_AT = 'data_alteracao';

    const DELETED_AT = 'data_exclusao';

    protected $fillable = [
        'nome',
        'descricao',
        'modulo',
        'ativo',
    ];

    protected $casts = [
        'ativo' => 'boolean',
    ];

    public function grupos(): BelongsToMany
    {
        return $this->belongsToMany(Grupo::class, 'grupo_permissao', 'permissao_id', 'grupo_id')
            ->withPivot('data_cadastro');
    }

    public function usuarios(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'usuario_permissao', 'permissao_id', 'usuario_id')
            ->withPivot('tipo', 'data_cadastro');
    }

    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }

    public function scopePorModulo($query, string $modulo)
    {
        return $query->where('modulo', $modulo);
    }

    public static function modulos(): array
    {
        return static::query()
            ->select('modulo')
            ->distinct()
            ->orderBy('modulo')
            ->pluck('modulo')
            ->toArray();
    }
}
