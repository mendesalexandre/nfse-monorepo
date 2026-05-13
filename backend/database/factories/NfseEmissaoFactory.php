<?php

namespace Database\Factories;

use App\Models\Cliente;
use App\Models\NfseEmissao;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NfseEmissao>
 */
class NfseEmissaoFactory extends Factory
{
    protected $model = NfseEmissao::class;

    public function definition(): array
    {
        return [
            'cliente_id' => Cliente::factory(),
            'chave_acesso' => str_pad((string) fake()->unique()->numberBetween(1, 99999999999999), 50, '0', STR_PAD_LEFT),
            'numero_nfse' => (string) fake()->numberBetween(1, 99999999),
            'c_stat' => 100,
            'x_motivo' => 'Operacao realizada com sucesso',
            'numero_dps' => fake()->unique()->numberBetween(1, 99999999),
            'serie_dps' => '1',
            'tomador_documento_encrypted' => '11144477735',
            'tomador_nome_encrypted' => fake()->name(),
            'valor_servicos' => fake()->randomFloat(2, 50, 500),
            'valor_iss' => fake()->randomFloat(2, 1, 20),
            'discriminacao_encrypted' => 'Servico de teste — discriminacao mockada.',
            'request_payload' => ['mock' => true],
            'response_xml_encrypted' => '<xml>mock</xml>',
            'data_emissao' => now(),
            'data_processamento' => now(),
            'status' => 'emitida',
        ];
    }
}
