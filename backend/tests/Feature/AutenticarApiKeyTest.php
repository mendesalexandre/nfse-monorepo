<?php

use App\Models\Cliente;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Rota de probe pra testar middleware isoladamente
    Route::middleware('autenticar.api-key')->get('/test-api-key-probe', function (Request $r) {
        $cliente = $r->attributes->get('cliente');

        return ['cliente_id' => $cliente?->id, 'cnpj' => $cliente?->cnpj];
    });

    Cache::flush();
    RateLimiter::clear('nfse-api:cliente:1');
});

it('retorna 401 quando o header X-Api-Key está ausente', function () {
    $resp = $this->getJson('/test-api-key-probe');
    $resp->assertStatus(401)->assertJsonPath('message', 'Header X-Api-Key obrigatório.');
});

it('retorna 401 quando a API key não bate com nenhum cliente ativo', function () {
    Cliente::factory()->comApiKey('valid_key_123')->create();

    $resp = $this->withHeaders(['X-Api-Key' => 'wrong_key'])->getJson('/test-api-key-probe');
    $resp->assertStatus(401);
});

it('retorna 401 quando o cliente está inativo', function () {
    Cliente::factory()->comApiKey('inactive_key')->inativo()->create();

    $resp = $this->withHeaders(['X-Api-Key' => 'inactive_key'])->getJson('/test-api-key-probe');
    $resp->assertStatus(401);
});

it('retorna 200 e popula o cliente quando a API key é válida', function () {
    $cliente = Cliente::factory()->comApiKey('valid_smoke_key')->create();

    $resp = $this->withHeaders(['X-Api-Key' => 'valid_smoke_key'])->getJson('/test-api-key-probe');
    $resp->assertStatus(200)
        ->assertJson([
            'cliente_id' => $cliente->id,
            'cnpj' => $cliente->cnpj,
        ]);
});

it('aplica rate limit de 100 req/min por cliente', function () {
    $cliente = Cliente::factory()->comApiKey('rate_limit_key')->create();
    $headers = ['X-Api-Key' => 'rate_limit_key'];

    // Hit 100x — todas devem passar
    for ($i = 0; $i < 100; $i++) {
        RateLimiter::hit('nfse-api:cliente:'.$cliente->id, 60);
    }

    // 101a deve ser bloqueada
    $resp = $this->withHeaders($headers)->getJson('/test-api-key-probe');
    $resp->assertStatus(429)->assertJsonStructure(['message', 'retry_after_seconds']);
});
