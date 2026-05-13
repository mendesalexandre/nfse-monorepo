<?php

namespace App\Filament\Resources\AuditLogs\Tables;

use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AuditLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('data_criacao')
                    ->label('Data')
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable(),
                TextColumn::make('cliente.nome_empresa')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—'),
                TextColumn::make('acao')
                    ->label('Ação')
                    ->badge()
                    ->color(fn (?string $state): string => match (true) {
                        str_starts_with((string) $state, 'nfse.cancelar') => 'danger',
                        str_starts_with((string) $state, 'nfse.emitir') => 'success',
                        str_starts_with((string) $state, 'nfse.consultar') => 'info',
                        default => 'gray',
                    })
                    ->searchable(),
                TextColumn::make('recurso_tipo')
                    ->label('Recurso')
                    ->placeholder('—'),
                TextColumn::make('recurso_id')
                    ->label('ID')
                    ->placeholder('—'),
                TextColumn::make('ip_origem')
                    ->label('IP')
                    ->placeholder('—')
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('cliente_id')
                    ->label('Cliente')
                    ->relationship('cliente', 'nome_empresa')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('acao')
                    ->label('Ação')
                    ->options([
                        'nfse.emitir' => 'Emitir',
                        'nfse.consultar' => 'Consultar',
                        'nfse.cancelar' => 'Cancelar',
                    ]),
                Filter::make('data')
                    ->schema([
                        DatePicker::make('de')->label('De'),
                        DatePicker::make('ate')->label('Até'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['de'] ?? null, fn ($q, $d) => $q->whereDate('data_criacao', '>=', $d))
                            ->when($data['ate'] ?? null, fn ($q, $d) => $q->whereDate('data_criacao', '<=', $d));
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([])
            ->defaultSort('data_criacao', 'desc')
            ->paginated([25, 50, 100]);
    }
}
