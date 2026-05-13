<?php

namespace App\Filament\Resources\NfseEmissaos\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class NfseEmissaoInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Cabeçalho')
                    ->columns(3)
                    ->components([
                        TextEntry::make('cliente.nome_empresa')
                            ->label('Cliente'),
                        TextEntry::make('chave_acesso')
                            ->label('Chave de acesso')
                            ->copyable()
                            ->columnSpan(2),
                        TextEntry::make('numero_nfse')
                            ->label('Nº NFS-e')
                            ->placeholder('—'),
                        TextEntry::make('numero_dps')
                            ->label('Nº DPS'),
                        TextEntry::make('serie_dps')
                            ->label('Série DPS'),
                        TextEntry::make('status')
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
                        TextEntry::make('c_stat')
                            ->label('cStat')
                            ->placeholder('—'),
                        TextEntry::make('x_motivo')
                            ->label('xMotivo SEFIN')
                            ->placeholder('—')
                            ->columnSpan(3),
                        TextEntry::make('data_emissao')
                            ->label('Data emissão')
                            ->dateTime('d/m/Y H:i:s'),
                        TextEntry::make('data_processamento')
                            ->label('Data processamento')
                            ->dateTime('d/m/Y H:i:s')
                            ->placeholder('—'),
                        TextEntry::make('data_criacao')
                            ->label('Registro criado em')
                            ->dateTime('d/m/Y H:i:s'),
                    ]),

                Section::make('Tomador')
                    ->description('Dados criptografados em repouso (LGPD), descifrados apenas pra exibição.')
                    ->columns(2)
                    ->components([
                        TextEntry::make('tomador_documento_encrypted')
                            ->label('Documento (CPF/CNPJ)')
                            ->placeholder('—'),
                        TextEntry::make('tomador_nome_encrypted')
                            ->label('Nome')
                            ->placeholder('—'),
                    ]),

                Section::make('Discriminação e valores')
                    ->columns(3)
                    ->components([
                        TextEntry::make('discriminacao_encrypted')
                            ->label('Discriminação')
                            ->placeholder('—')
                            ->columnSpan(3),
                        TextEntry::make('valor_servicos')
                            ->label('Valor serviços')
                            ->money('BRL'),
                        TextEntry::make('valor_iss')
                            ->label('Valor ISS')
                            ->money('BRL'),
                    ]),

                Section::make('Request payload')
                    ->collapsible()
                    ->collapsed()
                    ->components([
                        TextEntry::make('request_payload')
                            ->label('')
                            ->placeholder('— payload não armazenado —')
                            ->formatStateUsing(fn ($state): string => is_array($state)
                                ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                                : (string) $state)
                            ->prose()
                            ->copyable()
                            ->columnSpanFull(),
                    ]),

                Section::make('Response XML SEFIN')
                    ->collapsible()
                    ->collapsed()
                    ->components([
                        TextEntry::make('response_xml_encrypted')
                            ->label('')
                            ->placeholder('— sem retorno gravado —')
                            ->prose()
                            ->copyable()
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
