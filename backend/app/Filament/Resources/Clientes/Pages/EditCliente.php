<?php

namespace App\Filament\Resources\Clientes\Pages;

use App\Filament\Resources\Clientes\ClienteResource;
use App\Models\Cliente;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\HtmlString;
use PhpNfseNacional\Certificate\Certificate;
use PhpNfseNacional\Enums\RegimeEspecialTributacao;
use PhpNfseNacional\Enums\SituacaoSimplesNacional;
use PhpNfseNacional\Exceptions\CertificateException;

class EditCliente extends EditRecord
{
    protected static string $resource = ClienteResource::class;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('cliente_tabs')
                    ->tabs([
                        Tab::make('Dados')
                            ->icon('heroicon-o-identification')
                            ->schema(self::dadosSchema()),

                        Tab::make('Credenciais')
                            ->icon('heroicon-o-key')
                            ->schema(self::credenciaisSchema()),

                        Tab::make('Certificado')
                            ->icon('heroicon-o-document-check')
                            ->schema(self::certificadoSchema()),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    /**
     * @return array<\Filament\Schemas\Components\Component>
     */
    protected static function dadosSchema(): array
    {
        return [
            Section::make('Empresa')
                ->columns(2)
                ->components([
                    TextInput::make('nome_empresa')
                        ->label('Nome da empresa')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),
                    TextInput::make('cnpj')
                        ->label('CNPJ')
                        ->required()
                        ->mask('99.999.999/9999-99')
                        ->maxLength(20),
                    TextInput::make('email')
                        ->label('E-mail')
                        ->email()
                        ->required(),
                    TextInput::make('telefone')
                        ->label('Telefone')
                        ->tel(),
                ]),

            Section::make('Endereço do prestador')
                ->columns(6)
                ->components([
                    TextInput::make('cep')
                        ->label('CEP')
                        ->required()
                        ->mask('99999-999')
                        ->columnSpan(2),
                    Select::make('uf')
                        ->label('UF')
                        ->required()
                        ->options(self::ufOptions())
                        ->searchable()
                        ->columnSpan(1),
                    TextInput::make('codigo_municipio_ibge')
                        ->label('Cód. município (IBGE)')
                        ->required()
                        ->numeric()
                        ->columnSpan(3),
                    TextInput::make('logradouro')->required()->columnSpan(4),
                    TextInput::make('numero')->required()->columnSpan(2),
                    TextInput::make('bairro')->required()->columnSpan(3),
                    TextInput::make('complemento')->columnSpan(3),
                ]),

            Section::make('Tributário')
                ->columns(2)
                ->components([
                    TextInput::make('inscricao_municipal')->label('Inscrição municipal')->required(),
                    TextInput::make('razao_social_prestador')->label('Razão social')->required(),
                    Select::make('regime_especial_tributacao')
                        ->label('Regime especial de tributação')
                        ->required()
                        ->options(self::regimeEspecialOptions()),
                    Select::make('simples_nacional')
                        ->label('Simples Nacional')
                        ->required()
                        ->options(self::simplesNacionalOptions()),
                ]),

            Section::make('NFS-e')
                ->columns(3)
                ->components([
                    Toggle::make('ambiente')
                        ->label('Produção')
                        ->dehydrateStateUsing(fn ($state): string => $state ? 'producao' : 'homologacao')
                        ->formatStateUsing(fn ($state): bool => $state === 'producao'),
                    Toggle::make('incluir_ibscbs')->label('Incluir IBS/CBS'),
                    Toggle::make('is_ativo')->label('Ativo'),
                ]),
        ];
    }

    /**
     * @return array<\Filament\Schemas\Components\Component>
     */
    protected static function credenciaisSchema(): array
    {
        return [
            Section::make('Credenciais da API')
                ->description('Use os botões abaixo pra regenerar quando suspeitar de comprometimento. A nova chave é exibida UMA vez.')
                ->columns(2)
                ->components([
                    Placeholder::make('client_id_show')
                        ->label('Client ID')
                        ->content(fn (Cliente $record): string => $record->client_id ?? '—'),
                    Placeholder::make('api_key_show')
                        ->label('API Key (preview)')
                        ->content(fn (Cliente $record): string => $record->api_key_hash
                            ? 'nfse_***** (hash bcrypt — chave em texto não fica armazenada)'
                            : 'sem credencial — gere uma nova'),
                    Placeholder::make('client_secret_show')
                        ->label('Client Secret')
                        ->content(fn (Cliente $record): string => $record->client_secret_hash
                            ? 'sk_***** (hash bcrypt)'
                            : 'sem credencial — gere uma nova'),
                    Placeholder::make('cred_help')
                        ->label('')
                        ->content(new HtmlString('<p class="text-sm text-gray-500">As ações de regenerar ficam no header da página.</p>')),
                ]),
        ];
    }

    /**
     * @return array<\Filament\Schemas\Components\Component>
     */
    protected static function certificadoSchema(): array
    {
        return [
            Section::make('Certificado A1 atual')
                ->columns(3)
                ->components([
                    Placeholder::make('cert_cnpj_show')
                        ->label('CNPJ do cert')
                        ->content(fn (Cliente $record): string => $record->cert_cnpj ?? '—'),
                    Placeholder::make('cert_validade_show')
                        ->label('Validade')
                        ->content(fn (Cliente $record): string => $record->cert_validade
                            ? $record->cert_validade->format('d/m/Y')
                            : '—'),
                    Placeholder::make('cert_status_show')
                        ->label('Status')
                        ->content(fn (Cliente $record): string => self::semaforoCertificado($record)),
                ]),

            Section::make('Subir novo PFX')
                ->description('Substitui o certificado atual. Validamos a senha antes de gravar.')
                ->columns(2)
                ->components([
                    FileUpload::make('pfx_arquivo_novo')
                        ->label('Arquivo PFX')
                        ->acceptedFileTypes(['application/x-pkcs12'])
                        ->maxSize(2048)
                        ->disk('local')
                        ->directory('tmp/certs')
                        ->storeFiles(false)
                        ->dehydrated(false),
                    TextInput::make('pfx_senha_nova')
                        ->label('Senha do PFX')
                        ->password()
                        ->dehydrated(false),
                ]),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            self::regenerarApiKeyHeaderAction(),
            self::regenerarClientSecretHeaderAction(),
            self::salvarPfxHeaderAction(),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    protected static function regenerarApiKeyHeaderAction(): Action
    {
        return Action::make('regenerar_api_key')
            ->label('Regenerar API Key')
            ->icon('heroicon-o-key')
            ->color('warning')
            ->visible(fn (): bool => auth()->user()?->temPermissao('cliente.gerar_credenciais') ?? false)
            ->requiresConfirmation()
            ->modalHeading('Regenerar API Key?')
            ->modalDescription('A chave atual deixará de funcionar imediatamente. A nova será exibida UMA vez.')
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

    protected static function regenerarClientSecretHeaderAction(): Action
    {
        return Action::make('regenerar_client_secret')
            ->label('Regenerar Client Secret')
            ->icon('heroicon-o-shield-check')
            ->color('warning')
            ->visible(fn (): bool => auth()->user()?->temPermissao('cliente.gerar_credenciais') ?? false)
            ->requiresConfirmation()
            ->modalHeading('Regenerar Client Secret?')
            ->modalDescription('O secret atual deixará de funcionar. O novo será exibido UMA vez.')
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

    protected function salvarPfxHeaderAction(): Action
    {
        return Action::make('salvar_pfx')
            ->label('Salvar novo PFX (aba Certificado)')
            ->icon('heroicon-o-document-arrow-up')
            ->color('primary')
            ->visible(fn (): bool => auth()->user()?->temPermissao('cliente.editar') ?? false)
            ->action(function (): void {
                /** @var Cliente $record */
                $record = $this->getRecord();
                $state = $this->form->getRawState();
                $arquivo = $state['pfx_arquivo_novo'] ?? null;
                $senha = $state['pfx_senha_nova'] ?? null;

                if (! $arquivo || ! $senha) {
                    Notification::make()
                        ->warning()
                        ->title('Selecione o arquivo PFX e informe a senha na aba "Certificado".')
                        ->send();

                    return;
                }

                if (is_array($arquivo)) {
                    $arquivo = reset($arquivo);
                }

                $path = is_object($arquivo) && method_exists($arquivo, 'getRealPath')
                    ? $arquivo->getRealPath()
                    : storage_path('app/'.(string) $arquivo);

                $conteudoPfx = @file_get_contents($path);
                if ($conteudoPfx === false) {
                    Notification::make()->danger()->title('Falha ao ler arquivo PFX.')->send();

                    return;
                }

                try {
                    $cert = Certificate::fromPfxContent($conteudoPfx, (string) $senha);
                } catch (CertificateException $e) {
                    Notification::make()->danger()->title('Senha do PFX inválida ou cert corrompido')->body($e->getMessage())->send();

                    return;
                }

                $record->update([
                    'pfx_encrypted' => $conteudoPfx,
                    'pfx_senha_encrypted' => (string) $senha,
                    'cert_cnpj' => $cert->cnpj !== '' ? $cert->cnpj : $record->cert_cnpj,
                    'cert_validade' => $cert->validade,
                ]);

                Notification::make()
                    ->success()
                    ->title('Certificado atualizado')
                    ->body('Validade: '.$cert->validade->format('d/m/Y'))
                    ->send();

                $this->refreshFormData(['cert_cnpj', 'cert_validade']);
            });
    }

    private static function semaforoCertificado(Cliente $cliente): string
    {
        if (! $cliente->cert_validade) {
            return 'sem certificado';
        }
        $dias = (int) now()->startOfDay()->diffInDays($cliente->cert_validade->copy()->startOfDay(), false);
        if ($dias < 0) {
            return 'VENCIDO há '.abs($dias).' dias';
        }
        if ($dias < 7) {
            return "URGENTE — vence em {$dias} dia(s)";
        }
        if ($dias < 30) {
            return "Atenção — vence em {$dias} dias";
        }

        return "OK — vence em {$dias} dias";
    }

    /** @return array<int, string> */
    private static function regimeEspecialOptions(): array
    {
        $opts = [];
        foreach (RegimeEspecialTributacao::cases() as $case) {
            $opts[$case->value] = "{$case->value} — {$case->name}";
        }

        return $opts;
    }

    /** @return array<int, string> */
    private static function simplesNacionalOptions(): array
    {
        $opts = [];
        foreach (SituacaoSimplesNacional::cases() as $case) {
            $opts[$case->value] = "{$case->value} — {$case->name}";
        }

        return $opts;
    }

    /** @return array<string, string> */
    private static function ufOptions(): array
    {
        return [
            'AC' => 'AC', 'AL' => 'AL', 'AP' => 'AP', 'AM' => 'AM', 'BA' => 'BA',
            'CE' => 'CE', 'DF' => 'DF', 'ES' => 'ES', 'GO' => 'GO', 'MA' => 'MA',
            'MT' => 'MT', 'MS' => 'MS', 'MG' => 'MG', 'PA' => 'PA', 'PB' => 'PB',
            'PR' => 'PR', 'PE' => 'PE', 'PI' => 'PI', 'RJ' => 'RJ', 'RN' => 'RN',
            'RS' => 'RS', 'RO' => 'RO', 'RR' => 'RR', 'SC' => 'SC', 'SP' => 'SP',
            'SE' => 'SE', 'TO' => 'TO',
        ];
    }
}
