<?php

namespace Database\Seeders;

use App\Models\Cliente;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use PhpNfseNacional\Certificate\Certificate;

/**
 * Cria o cliente de teste — Cartório de Registro de Imóveis de Sinop.
 *
 * Usa o cert real do cartório (válido até 02/2027) e configuração tributária:
 *   - IM 11408
 *   - Sinop/MT (cMun 5107909)
 *   - regime especial 0 (Nenhum) — único compatível com vDedRed
 *   - Simples Nacional 1 (Não Optante)
 *   - ambiente Homologação
 *
 * Imprime credenciais determinísticas no console pra facilitar testes:
 *   - X-Api-Key:    nfse_test_sinop_2026
 *   - client_id:    cli_sinop_001
 *   - client_secret: sk_sinop_2026_secret
 *
 * Em produção, use `Cliente::gerarApiKey()` / `gerarClientSecret()`.
 */
class ClienteCartorioSinopSeeder extends Seeder
{
    private const PFX_PATH = '/home/alexandre/code/SINOP/certificado_digital_a1_ecnpj_00179028000138.pfx';

    private const PFX_SENHA = '123456';

    private const API_KEY_PLAIN = 'nfse_test_sinop_2026';

    private const CLIENT_ID = 'cli_sinop_001';

    private const CLIENT_SECRET_PLAIN = 'sk_sinop_2026_secret';

    public function run(): void
    {
        if (! is_file(self::PFX_PATH)) {
            $this->command->warn(
                '[ClienteCartorioSinopSeeder] PFX não encontrado em '.self::PFX_PATH.
                ' — pulando seed do cliente.'
            );

            return;
        }

        // Carrega cert pra extrair validade real
        try {
            $cert = Certificate::fromPfxFile(self::PFX_PATH, self::PFX_SENHA);
        } catch (\Throwable $e) {
            $this->command->error('[ClienteCartorioSinopSeeder] erro ao abrir PFX: '.$e->getMessage());

            return;
        }

        $pfxBinario = file_get_contents(self::PFX_PATH);
        $pfxBase64 = base64_encode($pfxBinario);

        $cliente = Cliente::updateOrCreate(
            ['cnpj' => '00179028000138'],
            [
                'nome_empresa' => 'Cartório de Registro de Imóveis de Sinop',
                'email' => 'cartorio@cartoriosinop.com.br',
                'telefone' => '6635311199',

                // Credenciais (hash bcrypt das versões planas)
                'api_key_hash' => Hash::make(self::API_KEY_PLAIN),
                'client_id' => self::CLIENT_ID,
                'client_secret_hash' => Hash::make(self::CLIENT_SECRET_PLAIN),

                // Cert
                'pfx_encrypted' => $pfxBase64, // será cifrado via cast 'encrypted'
                'pfx_senha_encrypted' => self::PFX_SENHA,
                'cert_validade' => $cert->validade->format('Y-m-d'),
                'cert_cnpj' => $cert->cnpj ?: '00179028000138',

                // Prestador
                'inscricao_municipal' => '11408',
                'razao_social_prestador' => 'CARTORIO DE REGISTRO DE IMOVEIS DE SINOP',
                'codigo_municipio_ibge' => '5107909', // Sinop/MT
                'uf' => 'MT',
                'cep' => '78550200',
                'logradouro' => 'Avenida das Itaubas',
                'numero' => '4982',
                'bairro' => 'Centro',
                'complemento' => null,

                // Tributação
                'regime_especial_tributacao' => 0, // Nenhum (único compatível com vDedRed)
                'simples_nacional' => 1,           // Não Optante

                // Toggles
                'ambiente' => 'homologacao',
                'incluir_ibscbs' => false,
                'is_ativo' => true,
            ],
        );

        $this->command->info('==========================================================');
        $this->command->info('Cliente de teste criado: '.$cliente->nome_empresa);
        $this->command->info('==========================================================');
        $this->command->info('  X-Api-Key:    '.self::API_KEY_PLAIN);
        $this->command->info('  client_id:    '.self::CLIENT_ID);
        $this->command->info('  client_secret: '.self::CLIENT_SECRET_PLAIN);
        $this->command->info('  Ambiente:    '.$cliente->ambiente);
        $this->command->info('  Cert valido até: '.$cliente->cert_validade->format('d/m/Y'));
        $this->command->info('==========================================================');
    }
}
