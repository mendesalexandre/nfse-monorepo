<?php

use App\Models\Cliente;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Seeda permissões + admin para os testes
    $this->seed();

    $this->adminUser = User::where('email', 'suporte@sistemaoslo.com.br')->first();
});

it('lista clientes paginado', function () {
    $this->actingAs($this->adminUser);

    Cliente::factory()->count(3)->create();

    $resp = $this->getJson('/api/admin/clientes');

    $resp->assertStatus(200)
        ->assertJsonStructure(['data', 'meta' => ['total', 'per_page']]);
});

it('cria cliente e retorna credenciais uma vez', function () {
    $this->actingAs($this->adminUser);

    $payload = [
        'nome_empresa' => 'Cartório Teste',
        'cnpj' => '12345678000190',
        'email' => 'teste@cartorio.com',
        'telefone' => '6535551234',
        'cep' => '78550000',
        'uf' => 'MT',
        'codigo_municipio_ibge' => '5107909',
        'logradouro' => 'Rua Teste',
        'numero' => '100',
        'bairro' => 'Centro',
        'inscricao_municipal' => '12345',
        'razao_social_prestador' => 'CARTORIO TESTE LTDA',
        'regime_especial_tributacao' => 0,
        'simples_nacional' => 1,
        'ambiente' => 'homologacao',
        'incluir_ibscbs' => false,
        'is_ativo' => true,
    ];

    $resp = $this->postJson('/api/admin/clientes', $payload);

    $resp->assertStatus(201)
        ->assertJsonStructure([
            'cliente' => ['id', 'nome_empresa', 'cnpj', 'client_id'],
            'credenciais' => ['api_key', 'client_id', 'client_secret', 'aviso'],
        ]);

    expect(Cliente::where('cnpj', '12345678000190')->exists())->toBeTrue();
});

it('rejeita criação sem campos obrigatórios', function () {
    $this->actingAs($this->adminUser);

    $resp = $this->postJson('/api/admin/clientes', []);

    $resp->assertStatus(422);
});

it('atualiza cliente existente', function () {
    $this->actingAs($this->adminUser);

    $cliente = Cliente::factory()->create(['nome_empresa' => 'Antigo']);

    $resp = $this->putJson("/api/admin/clientes/{$cliente->id}", [
        'nome_empresa' => 'Novo Nome',
        'cnpj' => $cliente->cnpj,
        'email' => 'novo@email.com',
        'cep' => '78550000',
        'uf' => 'MT',
        'codigo_municipio_ibge' => '5107909',
        'logradouro' => 'Rua X',
        'numero' => '1',
        'bairro' => 'Centro',
        'inscricao_municipal' => '11408',
        'razao_social_prestador' => 'TESTE',
        'regime_especial_tributacao' => 0,
        'simples_nacional' => 1,
        'ambiente' => 'homologacao',
        'incluir_ibscbs' => false,
        'is_ativo' => true,
    ]);

    $resp->assertStatus(200);
    expect($cliente->refresh()->nome_empresa)->toBe('Novo Nome');
});

it('regenera api key e retorna nova plana', function () {
    $this->actingAs($this->adminUser);
    $cliente = Cliente::factory()->create();
    $hashAntigo = $cliente->api_key_hash;

    $resp = $this->postJson("/api/admin/clientes/{$cliente->id}/regenerar-api-key");

    $resp->assertStatus(200)
        ->assertJsonStructure(['api_key', 'aviso']);

    expect($cliente->refresh()->api_key_hash)
        ->not->toBe($hashAntigo)
        ->and(str_starts_with($resp->json('api_key'), 'nfse_'))->toBeTrue();
});

it('regenera client secret', function () {
    $this->actingAs($this->adminUser);
    $cliente = Cliente::factory()->create();

    $resp = $this->postJson("/api/admin/clientes/{$cliente->id}/regenerar-client-secret");

    $resp->assertStatus(200)
        ->assertJsonStructure(['client_secret', 'aviso']);

    expect(str_starts_with($resp->json('client_secret'), 'sk_'))->toBeTrue();
});

it('revoga credenciais zerando os hashes', function () {
    $this->actingAs($this->adminUser);
    $cliente = Cliente::factory()->create();

    $resp = $this->postJson("/api/admin/clientes/{$cliente->id}/revogar");

    $resp->assertStatus(200);
    $cliente->refresh();
    expect($cliente->api_key_hash)->toBe('');
    expect($cliente->client_secret_hash)->toBe('');
});

it('soft delete preserva no banco com data_exclusao', function () {
    $this->actingAs($this->adminUser);
    $cliente = Cliente::factory()->create();

    $this->deleteJson("/api/admin/clientes/{$cliente->id}")
        ->assertStatus(200);

    expect(Cliente::find($cliente->id))->toBeNull();
    expect(Cliente::withTrashed()->find($cliente->id))->not->toBeNull();
});

it('exige autenticação', function () {
    $resp = $this->getJson('/api/admin/clientes');
    $resp->assertStatus(401);
});

it('Resource nunca expõe campos encrypted', function () {
    $this->actingAs($this->adminUser);
    $cliente = Cliente::factory()->create();

    $resp = $this->getJson("/api/admin/clientes/{$cliente->id}");

    $body = $resp->json('data');
    expect($body)->not->toHaveKey('pfx_encrypted');
    expect($body)->not->toHaveKey('pfx_senha_encrypted');
    expect($body)->not->toHaveKey('api_key_hash');
    expect($body)->not->toHaveKey('client_secret_hash');
});
