<?php

namespace App\Filament\Widgets;

use App\Models\NfseEmissao;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class EmissoesPorDiaChart extends ChartWidget
{
    protected ?string $heading = 'NFS-e por dia (30 dias)';

    protected ?string $description = 'Quantidade diária por status (emitida, cancelada, rejeitada).';

    protected int|string|array $columnSpan = 'full';

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $de = Carbon::now()->subDays(29)->startOfDay();
        $ate = Carbon::now()->endOfDay();

        $period = CarbonPeriod::create($de, '1 day', $ate);
        $labels = [];
        $bucket = [];
        foreach ($period as $d) {
            $key = $d->format('Y-m-d');
            $labels[] = $d->format('d/m');
            $bucket[$key] = ['emitida' => 0, 'cancelada' => 0, 'rejeitada' => 0];
        }

        $rows = NfseEmissao::query()
            ->select(
                DB::raw('date(data_criacao) as dia'),
                'status',
                DB::raw('count(*) as total')
            )
            ->whereBetween('data_criacao', [$de, $ate])
            ->whereIn('status', ['emitida', 'cancelada', 'rejeitada'])
            ->groupBy('dia', 'status')
            ->get();

        foreach ($rows as $r) {
            $key = (string) $r->dia;
            if (! isset($bucket[$key])) {
                continue;
            }
            $bucket[$key][$r->status] = (int) $r->total;
        }

        $emitida = [];
        $cancelada = [];
        $rejeitada = [];
        foreach ($bucket as $b) {
            $emitida[] = $b['emitida'];
            $cancelada[] = $b['cancelada'];
            $rejeitada[] = $b['rejeitada'];
        }

        return [
            'datasets' => [
                [
                    'label' => 'Emitida',
                    'data' => $emitida,
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.15)',
                    'tension' => 0.3,
                ],
                [
                    'label' => 'Cancelada',
                    'data' => $cancelada,
                    'borderColor' => '#6b7280',
                    'backgroundColor' => 'rgba(107, 114, 128, 0.15)',
                    'tension' => 0.3,
                ],
                [
                    'label' => 'Rejeitada',
                    'data' => $rejeitada,
                    'borderColor' => '#ef4444',
                    'backgroundColor' => 'rgba(239, 68, 68, 0.15)',
                    'tension' => 0.3,
                ],
            ],
            'labels' => $labels,
        ];
    }
}
