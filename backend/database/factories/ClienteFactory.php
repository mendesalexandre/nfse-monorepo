<?php

namespace Database\Factories;

use App\Models\Cliente;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<Cliente>
 */
class ClienteFactory extends Factory
{
    protected $model = Cliente::class;

    public function definition(): array
    {
        return [
            'nome_empresa' => fake()->company(),
            'cnpj' => preg_replace('/\D/', '', fake()->unique()->numerify('##############')),
            'email' => fake()->companyEmail(),
            'telefone' => fake()->numerify('65#########'),

            'api_key_hash' => Hash::make('test_key_default'),
            'client_id' => 'cli_'.fake()->unique()->bothify('????????????????????????????'),
            'client_secret_hash' => Hash::make('test_secret_default'),

            // Cert dummy (não funciona pra emissão real — mas serve pra testes que mockam o SDK)
            'pfx_encrypted' => base64_encode('FAKE_PFX_BINARY_FOR_TESTS'),
            'pfx_senha_encrypted' => '123456',
            'cert_validade' => now()->addYear()->format('Y-m-d'),
            'cert_cnpj' => '00000000000000',

            'inscricao_municipal' => '11408',
            'razao_social_prestador' => fake()->company(),
            'codigo_municipio_ibge' => '5107909',
            'uf' => 'MT',
            'cep' => '78550200',
            'logradouro' => fake()->streetName(),
            'numero' => (string) fake()->numberBetween(1, 9999),
            'bairro' => 'Centro',

            'regime_especial_tributacao' => 0,
            'simples_nacional' => 1,
            'ambiente' => 'homologacao',
            'incluir_ibscbs' => false,
            'is_ativo' => true,
        ];
    }

    public function comApiKey(string $apiKey): static
    {
        return $this->state(fn () => [
            'api_key_hash' => Hash::make($apiKey),
        ]);
    }

    public function inativo(): static
    {
        return $this->state(fn () => ['is_ativo' => false]);
    }
}
