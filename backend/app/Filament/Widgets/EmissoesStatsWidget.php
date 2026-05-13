<?php

namespace App\Filament\Widgets;

use App\Models\NfseEmissao;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class EmissoesStatsWidget extends StatsOverviewWidget
{
    protected ?string $heading = 'NFS-e';

    protected function getStats(): array
    {
        $inicioHoje = Carbon::now()->startOfDay();
        $h30 = Carbon::now()->subDays(30);

        $emitidasHoje = NfseEmissao::query()
            ->where('status', 'emitida')
            ->where('data_criacao', '>=', $inicioHoje)
            ->count();

        $emitidas30 = NfseEmissao::query()
            ->where('status', 'emitida')
            ->where('data_criacao', '>=', $h30)
            ->count();

        $falhas30 = NfseEmissao::query()
            ->whereIn('status', ['rejeitada', 'erro'])
            ->where('data_criacao', '>=', $h30)
            ->count();

        $cancel30 = NfseEmissao::query()
            ->where('status', 'cancelada')
            ->where('data_criacao', '>=', $h30)
            ->count();

        return [
            Stat::make('Emitidas hoje', (string) $emitidasHoje)
                ->description($inicioHoje->format('d/m/Y'))
                ->color('success')
                ->icon('heroicon-o-document-check'),

            Stat::make('Emitidas (30d)', (string) $emitidas30)
                ->description('últimos 30 dias')
                ->color('success')
                ->icon('heroicon-o-document-text'),

            Stat::make('Falhas (30d)', (string) $falhas30)
                ->description('rejeitadas + erro')
                ->color($falhas30 > 0 ? 'danger' : 'gray')
                ->icon('heroicon-o-x-circle'),

            Stat::make('Cancelamentos (30d)', (string) $cancel30)
                ->description('via SEFIN')
                ->color('warning')
                ->icon('heroicon-o-no-symbol'),
        ];
    }
}
