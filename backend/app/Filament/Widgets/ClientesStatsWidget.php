<?php

namespace App\Filament\Widgets;

use App\Models\Cliente;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ClientesStatsWidget extends StatsOverviewWidget
{
    protected ?string $heading = 'Clientes';

    protected function getStats(): array
    {
        $hoje = Carbon::now()->startOfDay();
        $em30 = $hoje->copy()->addDays(30);

        $ativos = Cliente::query()->where('is_ativo', true)->count();

        $expirando = Cliente::query()
            ->whereBetween('cert_validade', [$hoje, $em30])
            ->count();

        $vencidos = Cliente::query()
            ->whereNotNull('cert_validade')
            ->where('cert_validade', '<', $hoje)
            ->count();

        $semCredenciais = Cliente::query()
            ->where(function ($q) {
                $q->whereNull('api_key_hash')->orWhere('is_ativo', false);
            })
            ->count();

        return [
            Stat::make('Clientes ativos', (string) $ativos)
                ->description('com is_ativo=true')
                ->color('success')
                ->icon('heroicon-o-building-office-2'),

            Stat::make('Cert. expirando', (string) $expirando)
                ->description('próximos 30 dias')
                ->color($expirando > 0 ? 'warning' : 'gray')
                ->icon('heroicon-o-clock'),

            Stat::make('Cert. vencidos', (string) $vencidos)
                ->description('precisa renovar')
                ->color($vencidos > 0 ? 'danger' : 'gray')
                ->icon('heroicon-o-exclamation-triangle'),

            Stat::make('Sem credenciais', (string) $semCredenciais)
                ->description('api_key_hash null OU inativo')
                ->color('gray')
                ->icon('heroicon-o-key'),
        ];
    }
}
