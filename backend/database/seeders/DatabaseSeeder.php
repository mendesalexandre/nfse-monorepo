<?php

namespace Database\Seeders;

use App\Models\Grupo;
use App\Models\Permissao;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Grupo Administrador (acesso total via bypass)
        $admin = Grupo::firstOrCreate(['nome' => 'Administrador'], [
            'descricao' => 'Acesso total ao sistema',
        ]);

        // Permissões base do painel da API
        $modulos = [
            'usuarios' => ['listar', 'criar', 'editar', 'excluir'],
            'grupos' => ['listar', 'criar', 'editar', 'excluir'],
            'permissoes' => ['listar', 'criar', 'editar', 'excluir'],

            // Domínio NFS-e
            'nfse' => ['emitir', 'cancelar', 'consultar'],
            'cliente' => ['listar', 'criar', 'editar', 'gerar_credenciais', 'revogar'],
        ];

        foreach ($modulos as $modulo => $acoes) {
            foreach ($acoes as $acao) {
                Permissao::firstOrCreate(
                    ['nome' => "{$modulo}.{$acao}"],
                    ['descricao' => ucfirst($acao).' '.$modulo, 'modulo' => $modulo],
                );
            }
        }

        // Usuário admin
        $user = User::firstOrCreate(
            ['email' => 'suporte@sistemaoslo.com.br'],
            [
                'nome' => 'Administrador',
                'senha' => 'password',
            ],
        );

        if (! $user->grupos()->where('grupo_id', $admin->id)->exists()) {
            $user->grupos()->attach($admin->id);
        }

        // Cliente de teste — Cartório de Sinop
        $this->call(ClienteCartorioSinopSeeder::class);
    }
}
