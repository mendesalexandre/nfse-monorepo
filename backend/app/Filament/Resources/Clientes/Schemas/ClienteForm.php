<?php

namespace App\Filament\Resources\Clientes\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use PhpNfseNacional\Enums\RegimeEspecialTributacao;
use PhpNfseNacional\Enums\SituacaoSimplesNacional;

class ClienteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
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
                            ->maxLength(20)
                            ->unique(ignoreRecord: true),
                        TextInput::make('email')
                            ->label('E-mail')
                            ->email()
                            ->required()
                            ->maxLength(255),
                        TextInput::make('telefone')
                            ->label('Telefone')
                            ->tel()
                            ->maxLength(40),
                    ]),

                Section::make('Endereço do prestador')
                    ->columns(6)
                    ->components([
                        TextInput::make('cep')
                            ->label('CEP')
                            ->required()
                            ->mask('99999-999')
                            ->maxLength(20)
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
                            ->maxLength(7)
                            ->columnSpan(3),
                        TextInput::make('logradouro')
                            ->label('Logradouro')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(4),
                        TextInput::make('numero')
                            ->label('Número')
                            ->required()
                            ->maxLength(20)
                            ->columnSpan(2),
                        TextInput::make('bairro')
                            ->label('Bairro')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(3),
                        TextInput::make('complemento')
                            ->label('Complemento')
                            ->maxLength(255)
                            ->columnSpan(3),
                    ]),

                Section::make('Tributário')
                    ->columns(2)
                    ->components([
                        TextInput::make('inscricao_municipal')
                            ->label('Inscrição municipal')
                            ->required()
                            ->maxLength(40),
                        TextInput::make('razao_social_prestador')
                            ->label('Razão social')
                            ->required()
                            ->maxLength(255),
                        Select::make('regime_especial_tributacao')
                            ->label('Regime especial de tributação')
                            ->required()
                            ->options(self::regimeEspecialOptions())
                            ->default(0),
                        Select::make('simples_nacional')
                            ->label('Situação Simples Nacional')
                            ->required()
                            ->options(self::simplesNacionalOptions())
                            ->default(2),
                    ]),

                Section::make('NFS-e')
                    ->columns(3)
                    ->components([
                        Toggle::make('ambiente')
                            ->label('Produção (desligado = homologação)')
                            ->dehydrateStateUsing(fn ($state): string => $state ? 'producao' : 'homologacao')
                            ->formatStateUsing(fn ($state): bool => $state === 'producao')
                            ->default(false),
                        Toggle::make('incluir_ibscbs')
                            ->label('Incluir IBS/CBS')
                            ->default(false),
                        Toggle::make('is_ativo')
                            ->label('Ativo')
                            ->default(true),
                    ]),
            ]);
    }

    /**
     * @return array<int, string>
     */
    private static function regimeEspecialOptions(): array
    {
        $opts = [];
        foreach (RegimeEspecialTributacao::cases() as $case) {
            $opts[$case->value] = "{$case->value} — ".(method_exists($case, 'label') ? $case->label() : $case->name);
        }

        return $opts;
    }

    /**
     * @return array<int, string>
     */
    private static function simplesNacionalOptions(): array
    {
        $opts = [];
        foreach (SituacaoSimplesNacional::cases() as $case) {
            $opts[$case->value] = "{$case->value} — ".(method_exists($case, 'label') ? $case->label() : $case->name);
        }

        return $opts;
    }

    /**
     * @return array<string, string>
     */
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
