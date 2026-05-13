<?php

namespace App\Filament\Resources\Clientes\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class NfseEmissoesRelationManager extends RelationManager
{
    protected static string $relationship = 'nfsesEmissoes';

    protected static ?string $title = 'Emissões NFS-e';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('chave_acesso')
            ->columns([
                TextColumn::make('data_emissao')
                    ->label('Emissão')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('chave_acesso')
                    ->label('Chave')
                    ->limit(8)
                    ->tooltip(fn ($record): ?string => $record?->chave_acesso)
                    ->searchable()
                    ->copyable(),
                TextColumn::make('numero_nfse')
                    ->label('Número')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'emitida' => 'success',
                        'cancelada' => 'gray',
                        'rejeitada' => 'danger',
                        'pendente' => 'warning',
                        'erro' => 'danger',
                        'substituida' => 'info',
                        default => 'gray',
                    }),
                TextColumn::make('valor_servicos')
                    ->label('Valor (R$)')
                    ->money('BRL')
                    ->sortable(),
            ])
            ->defaultSort('data_emissao', 'desc')
            ->paginated([10, 25, 50])
            ->headerActions([])
            ->recordActions([])
            ->toolbarActions([]);
    }
}
