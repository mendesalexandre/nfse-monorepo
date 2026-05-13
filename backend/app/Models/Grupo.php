<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Grupo extends Model
{
    use SoftDeletes;

    protected $table = 'grupo';

    const CREATED_AT = 'data_cadastro';

    const UPDATED_AT = 'data_alteracao';

    const DELETED_AT = 'data_exclusao';

    protected $fillable = [
        'nome',
        'descricao',
        'ativo',
    ];

    protected $casts = [
        'ativo' => 'boolean',
    ];

    public function permissoes(): BelongsToMany
    {
        return $this->belongsToMany(Permissao::class, 'grupo_permissao', 'grupo_id', 'permissao_id')
            ->withPivot('data_cadastro');
    }

    public function usuarios(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'usuario_grupo', 'grupo_id', 'usuario_id')
            ->withPivot('data_cadastro');
    }

    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }
}
