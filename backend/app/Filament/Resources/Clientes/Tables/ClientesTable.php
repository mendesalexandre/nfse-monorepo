<?php

namespace App\Filament\Resources\Clientes\Tables;

use App\Models\Cliente;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use PhpNfseNacional\Certificate\Certificate;
use PhpNfseNacional\Exceptions\CertificateException;

class ClientesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nome_empresa')
                    ->label('Empresa')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),
                TextColumn::make('cnpj')
                    ->label('CNPJ')
                    ->searchable()
                    ->formatStateUsing(fn (?string $state): string => self::formatarCnpj($state)),
                TextColumn::make('ambiente')
                    ->label('Ambiente')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'producao' ? 'danger' : 'warning')
                    ->formatStateUsing(fn (string $state): string => $state === 'producao' ? 'Produção' : 'Homologação'),
                TextColumn::make('cert_validade')
                    ->label('Certificado')
                    ->date('d/m/Y')
                    ->sortable()
                    ->badge()
                    ->color(fn ($state): string => self::corValidadeCert($state))
                    ->description(fn ($state): ?string => self::descricaoValidadeCert($state)),
                IconColumn::make('is_ativo')
                    ->label('Ativo')
                    ->boolean(),
                TextColumn::make('data_criacao')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('ambiente')
                    ->label('Ambiente')
                    ->options([
                        'homologacao' => 'Homologação',
                        'producao' => 'Produção',
                    ]),
                TernaryFilter::make('is_ativo')
                    ->label('Status')
                    ->placeholder('Todos')
                    ->trueLabel('Apenas ativos')
                    ->falseLabel('Apenas inativos'),
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                self::regenerarApiKeyAction(),
                self::regenerarClientSecretAction(),
                self::uploadCertificadoAction(),
                self::revogarAction(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('nome_empresa');
    }

    private static function regenerarApiKeyAction(): Action
    {
        return Action::make('regenerar_api_key')
            ->label('Regenerar API Key')
            ->icon('heroicon-o-key')
            ->color('warning')
            ->visible(fn (): bool => auth()->user()?->temPermissao('cliente.gerar_credenciais') ?? false)
            ->requiresConfirmation()
            ->modalHeading('Regenerar API Key?')
            ->modalDescription('A chave atual deixará de funcionar imediatamente. A nova chave será exibida APENAS uma vez.')
            ->modalSubmitActionLabel('Sim, regenerar')
            ->action(function (Cliente $record): void {
                $plain = Cliente::gerarApiKey();
                $record->update(['api_key_hash' => Hash::make($plain)]);

                Notification::make()
                    ->success()
                    ->title('Nova API Key gerada')
                    ->body($plain)
                    ->persistent()
                    ->send();
            });
    }

    private static function regenerarClientSecretAction(): Action
    {
        return Action::make('regenerar_client_secret')
            ->label('Regenerar Client Secret')
            ->icon('heroicon-o-shield-check')
            ->color('warning')
            ->visible(fn (): bool => auth()->user()?->temPermissao('cliente.gerar_credenciais') ?? false)
            ->requiresConfirmation()
            ->modalHeading('Regenerar Client Secret?')
            ->modalDescription('O secret atual deixará de funcionar. O novo será exibido APENAS uma vez.')
            ->modalSubmitActionLabel('Sim, regenerar')
            ->action(function (Cliente $record): void {
                $plain = Cliente::gerarClientSecret();
                $record->update(['client_secret_hash' => Hash::make($plain)]);

                Notification::make()
                    ->success()
                    ->title('Novo Client Secret gerado')
                    ->body($plain)
                    ->persistent()
                    ->send();
            });
    }

    private static function uploadCertificadoAction(): Action
    {
        return Action::make('upload_certificado')
            ->label('Upload certificado')
            ->icon('heroicon-o-document-arrow-up')
            ->color('primary')
            ->visible(fn (): bool => auth()->user()?->temPermissao('cliente.editar') ?? false)
            ->schema([
                FileUpload::make('pfx_arquivo')
                    ->label('Arquivo PFX (.pfx)')
                    ->required()
                    ->acceptedFileTypes(['application/x-pkcs12'])
                    ->maxSize(2048) // 2 MB
                    ->disk('local')
                    ->directory('tmp/certs')
                    ->storeFiles(false),
                TextInput::make('pfx_senha')
                    ->label('Senha do PFX')
                    ->password()
                    ->required(),
            ])
            ->action(function (array $data, Cliente $record): void {
                /** @var \Illuminate\Http\UploadedFile $file */
                $file = $data['pfx_arquivo'];
                $conteudoPfx = file_get_contents($file->getRealPath());
                $senha = (string) $data['pfx_senha'];

                try {
                    $cert = Certificate::fromPfxContent($conteudoPfx, $senha);
                } catch (CertificateException $e) {
                    Notification::make()
                        ->danger()
                        ->title('Falha ao processar certificado')
                        ->body($e->getMessage())
                        ->send();

                    return;
                }

                $record->update([
                    'pfx_encrypted' => $conteudoPfx,
                    'pfx_senha_encrypted' => $senha,
                    'cert_cnpj' => $cert->cnpj !== '' ? $cert->cnpj : $record->cert_cnpj,
                    'cert_validade' => $cert->validade,
                ]);

                Notification::make()
                    ->success()
                    ->title('Certificado atualizado')
                    ->body('Validade: '.$cert->validade->format('d/m/Y'))
                    ->send();
            });
    }

    private static function revogarAction(): Action
    {
        return Action::make('revogar')
            ->label('Revogar acesso')
            ->icon('heroicon-o-no-symbol')
            ->color('danger')
            ->visible(fn (): bool => auth()->user()?->temPermissao('cliente.revogar') ?? false)
            ->requiresConfirmation()
            ->modalHeading('Revogar acesso deste cliente?')
            ->modalDescription('Apaga API Key e Client Secret e desativa o cliente. Não há como desfazer (você precisará gerar credenciais novas).')
            ->modalSubmitActionLabel('Sim, revogar')
            ->action(function (Cliente $record): void {
                $record->update([
                    'api_key_hash' => null,
                    'client_secret_hash' => null,
                    'is_ativo' => false,
                ]);

                Notification::make()
                    ->warning()
                    ->title('Cliente revogado')
                    ->body("Credenciais apagadas e cliente desativado: {$record->nome_empresa}")
                    ->send();
            });
    }

    private static function formatarCnpj(?string $cnpj): string
    {
        if (! $cnpj) {
            return '';
        }
        $digits = preg_replace('/\D/', '', $cnpj) ?? '';
        if (strlen($digits) !== 14) {
            return $cnpj;
        }

        return substr($digits, 0, 2).'.'.substr($digits, 2, 3).'.'.substr($digits, 5, 3).'/'.substr($digits, 8, 4).'-'.substr($digits, 12, 2);
    }

    private static function corValidadeCert(mixed $state): string
    {
        if (! $state) {
            return 'gray';
        }
        $data = $state instanceof Carbon ? $state : Carbon::parse((string) $state);
        $dias = (int) Carbon::now()->startOfDay()->diffInDays($data->copy()->startOfDay(), false);

        return match (true) {
            $dias < 7 => 'danger',
            $dias < 30 => 'warning',
            default => 'success',
        };
    }

    private static function descricaoValidadeCert(mixed $state): ?string
    {
        if (! $state) {
            return null;
        }
        $data = $state instanceof Carbon ? $state : Carbon::parse((string) $state);
        $dias = (int) Carbon::now()->startOfDay()->diffInDays($data->copy()->startOfDay(), false);

        if ($dias < 0) {
            return 'Vencido há '.abs($dias).' dia(s)';
        }
        if ($dias === 0) {
            return 'Vence hoje';
        }

        return "Vence em {$dias} dia(s)";
    }
}
