<?php

namespace App\Filament\Resources\NfseEmissaos\Tables;

use App\Models\NfseEmissao;
use App\Services\NFSe\NfseEmissorService;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response;
use PhpNfseNacional\Exceptions\SefinException;

class NfseEmissaosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('data_criacao')
                    ->label('Criada')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('cliente.nome_empresa')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable()
                    ->url(fn (NfseEmissao $record): ?string => $record->cliente_id
                        ? route('filament.admin.resources.clientes.edit', ['record' => $record->cliente_id])
                        : null),
                TextColumn::make('chave_acesso')
                    ->label('Chave')
                    ->limit(8)
                    ->tooltip(fn (NfseEmissao $record): ?string => $record->chave_acesso)
                    ->copyable()
                    ->searchable(),
                TextColumn::make('numero_nfse')
                    ->label('Nº NFS-e')
                    ->searchable()
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
                    })
                    ->sortable(),
                TextColumn::make('valor_servicos')
                    ->label('Valor (R$)')
                    ->money('BRL')
                    ->sortable(),
                TextColumn::make('c_stat')
                    ->label('cStat')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('cliente_id')
                    ->label('Cliente')
                    ->relationship('cliente', 'nome_empresa')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pendente' => 'Pendente',
                        'emitida' => 'Emitida',
                        'cancelada' => 'Cancelada',
                        'rejeitada' => 'Rejeitada',
                        'substituida' => 'Substituída',
                        'erro' => 'Erro',
                    ]),
                Filter::make('data_emissao')
                    ->schema([
                        DatePicker::make('de')->label('De'),
                        DatePicker::make('ate')->label('Até'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['de'] ?? null, fn ($q, $d) => $q->whereDate('data_emissao', '>=', $d))
                            ->when($data['ate'] ?? null, fn ($q, $d) => $q->whereDate('data_emissao', '<=', $d));
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
                self::baixarDanfseAction(),
                self::cancelarAction(),
            ])
            ->defaultSort('data_criacao', 'desc')
            ->paginated([25, 50, 100]);
    }

    private static function baixarDanfseAction(): Action
    {
        return Action::make('baixar_danfse')
            ->label('Baixar DANFSe')
            ->icon('heroicon-o-arrow-down-tray')
            ->color('primary')
            ->visible(fn (NfseEmissao $record): bool => $record->status === 'emitida'
                && (auth()->user()?->temPermissao('nfse.consultar') ?? false))
            ->action(function (NfseEmissao $record) {
                try {
                    $service = new NfseEmissorService($record->cliente);
                    $pdf = $service->gerarDanfsePdf($record->chave_acesso);
                } catch (\Throwable $e) {
                    Notification::make()
                        ->danger()
                        ->title('Falha ao gerar DANFSe')
                        ->body($e->getMessage())
                        ->send();

                    return null;
                }

                $filename = 'danfse-'.$record->chave_acesso.'.pdf';

                return response($pdf, Response::HTTP_OK, [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => "attachment; filename={$filename}",
                ]);
            });
    }

    private static function cancelarAction(): Action
    {
        return Action::make('cancelar_nfse')
            ->label('Cancelar NFS-e')
            ->icon('heroicon-o-no-symbol')
            ->color('danger')
            ->visible(fn (NfseEmissao $record): bool => $record->ehCancelavel()
                && (auth()->user()?->temPermissao('nfse.cancelar') ?? false))
            ->schema([
                Select::make('motivo')
                    ->label('Motivo do cancelamento')
                    ->required()
                    ->options([
                        1 => '1 — Erro na emissão',
                        2 => '2 — Serviço não prestado',
                        9 => '9 — Outros',
                    ]),
                Textarea::make('justificativa')
                    ->label('Justificativa')
                    ->required()
                    ->minLength(15)
                    ->maxLength(200)
                    ->rows(4)
                    ->helperText('Mínimo 15 caracteres, máximo 200.'),
            ])
            ->action(function (array $data, NfseEmissao $record): void {
                try {
                    $service = new NfseEmissorService($record->cliente);
                    $service->cancelar(
                        $record->chave_acesso,
                        (int) $data['motivo'],
                        (string) $data['justificativa'],
                        ['ip' => request()->ip(), 'user_agent' => request()->userAgent()]
                    );

                    Notification::make()
                        ->success()
                        ->title('NFS-e cancelada no SEFIN')
                        ->body("Chave: {$record->chave_acesso}")
                        ->send();
                } catch (SefinException $e) {
                    Notification::make()
                        ->danger()
                        ->title('SEFIN rejeitou cancelamento')
                        ->body("cStat={$e->cStat} — {$e->xMotivo}")
                        ->send();
                } catch (\Throwable $e) {
                    Notification::make()
                        ->danger()
                        ->title('Falha ao cancelar')
                        ->body($e->getMessage())
                        ->send();
                }
            });
    }
}
