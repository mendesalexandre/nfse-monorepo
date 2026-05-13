<?php

namespace App\Filament\Resources\AuditLogs\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AuditLogInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Resumo')
                    ->columns(3)
                    ->components([
                        TextEntry::make('data_criacao')
                            ->label('Data')
                            ->dateTime('d/m/Y H:i:s'),
                        TextEntry::make('cliente.nome_empresa')
                            ->label('Cliente')
                            ->placeholder('—'),
                        TextEntry::make('acao')
                            ->label('Ação')
                            ->badge(),
                        TextEntry::make('recurso_tipo')
                            ->label('Recurso')
                            ->placeholder('—'),
                        TextEntry::make('recurso_id')
                            ->label('ID do recurso')
                            ->placeholder('—'),
                        TextEntry::make('ip_origem')
                            ->label('IP')
                            ->placeholder('—'),
                        TextEntry::make('user_agent')
                            ->label('User-agent')
                            ->placeholder('—')
                            ->columnSpanFull(),
                    ]),

                Section::make('Dados do request')
                    ->collapsible()
                    ->collapsed()
                    ->components([
                        TextEntry::make('dados_request')
                            ->label('')
                            ->placeholder('—')
                            ->formatStateUsing(fn ($state): string => self::prettyJson($state))
                            ->prose()
                            ->copyable()
                            ->columnSpanFull(),
                    ]),

                Section::make('Dados do response')
                    ->collapsible()
                    ->collapsed()
                    ->components([
                        TextEntry::make('dados_response')
                            ->label('')
                            ->placeholder('—')
                            ->formatStateUsing(fn ($state): string => self::prettyJson($state))
                            ->prose()
                            ->copyable()
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    private static function prettyJson(mixed $state): string
    {
        if ($state === null || $state === '') {
            return '—';
        }
        if (is_string($state)) {
            $decoded = json_decode($state, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }

            return $state;
        }

        return json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
