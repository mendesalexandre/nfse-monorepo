<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $table = 'usuario';

    const CREATED_AT = 'data_cadastro';

    const UPDATED_AT = 'data_alteracao';

    const DELETED_AT = 'data_exclusao';

    protected $fillable = [
        'nome',
        'email',
        'senha',
        'is_ativo',
    ];

    protected $hidden = [
        'senha',
    ];

    protected function casts(): array
    {
        return [
            'senha' => 'hashed',
            'is_ativo' => 'boolean',
        ];
    }

    public function getAuthPassword(): string
    {
        return $this->senha;
    }

    /**
     * Filament — controla quem pode entrar no painel admin.
     *
     * Permite admins (bypass total) e qualquer usuário com permissão de
     * leitura básica de cliente ou consulta de NFS-e.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        if (! $this->is_ativo) {
            return false;
        }

        if ($this->isAdmin()) {
            return true;
        }

        return $this->temAlgumaPermissao(['cliente.listar', 'nfse.consultar']);
    }

    // ===== SISTEMA DE PERMISSÕES =====

    public function grupos(): BelongsToMany
    {
        return $this->belongsToMany(Grupo::class, 'usuario_grupo', 'usuario_id', 'grupo_id')
            ->withPivot('data_cadastro');
    }

    public function permissoesIndividuais(): BelongsToMany
    {
        return $this->belongsToMany(Permissao::class, 'usuario_permissao', 'usuario_id', 'permissao_id')
            ->withPivot('tipo', 'data_cadastro');
    }

    /**
     * Verifica se o usuário tem uma permissão específica.
     * Prioridade: admin bypass → negar individual → permitir individual → grupo
     */
    public function temPermissao(string $nome): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        // Permissão individual (negar tem prioridade)
        $individual = $this->permissoesIndividuais()
            ->where('nome', $nome)
            ->first();

        if ($individual) {
            return $individual->pivot->tipo === 'permitir';
        }

        // Permissão via grupo
        return $this->grupos()
            ->whereHas('permissoes', fn ($q) => $q->where('nome', $nome))
            ->exists();
    }

    public function temAlgumaPermissao(array $nomes): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        foreach ($nomes as $nome) {
            if ($this->temPermissao($nome)) {
                return true;
            }
        }

        return false;
    }

    public function temTodasPermissoes(array $nomes): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        foreach ($nomes as $nome) {
            if (! $this->temPermissao($nome)) {
                return false;
            }
        }

        return true;
    }

    public function pertenceAoGrupo(string $nome): bool
    {
        return $this->grupos()->where('nome', $nome)->exists();
    }

    public function isAdmin(): bool
    {
        return $this->grupos()->where('nome', 'Administrador')->exists();
    }

    /**
     * Retorna lista flat de nomes de permissões efetivas.
     */
    public function obterPermissoes(): array
    {
        if ($this->isAdmin()) {
            return Permissao::ativos()->pluck('nome')->toArray();
        }

        // Permissões dos grupos
        $grupoPermissoes = Permissao::whereHas('grupos', function ($q) {
            $q->whereIn('grupo.id', $this->grupos()->pluck('grupo.id'));
        })->pluck('nome')->toArray();

        // Permissões individuais
        $individuais = $this->permissoesIndividuais()->get();

        $permitidas = $individuais->where('pivot.tipo', 'permitir')->pluck('nome')->toArray();
        $negadas = $individuais->where('pivot.tipo', 'negar')->pluck('nome')->toArray();

        // Merge: grupo + permitidas individuais - negadas
        $todas = array_unique(array_merge($grupoPermissoes, $permitidas));
        $todas = array_diff($todas, $negadas);

        return array_values($todas);
    }

    /**
     * Retorna permissões efetivas agrupadas por módulo.
     */
    public function obterPermissoesPorModulo(): array
    {
        $nomes = $this->obterPermissoes();

        return Permissao::whereIn('nome', $nomes)
            ->orderBy('modulo')
            ->orderBy('nome')
            ->get()
            ->groupBy('modulo')
            ->map(fn ($perms) => $perms->pluck('nome')->toArray())
            ->toArray();
    }
}
