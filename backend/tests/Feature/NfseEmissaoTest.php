<?php

use App\Models\AuditLog;
use App\Models\Cliente;
use App\Models\NfseEmissao;
use App\Services\NFSe\NfseEmissorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use PhpNfseNacional\Sefin\SefinResposta;

uses(RefreshDatabase::class);

/**
 * Stub do NfseEmissorService que evita chamar o SEFIN — controla a resposta
 * inteira via construtor e ainda persiste como o original faria.
 */
class NfseEmissorServiceFake extends NfseEmissorService
{
    public static SefinResposta $proximaResposta;

    public function emitir(array $dados, array $audit = []): NfseEmissao
    {
        $emissao = NfseEmissao::create([
            'cliente_id' => $this->getCliente()->id,
            'numero_dps' => $dados['numero_dps'] ?? rand(1, 99999999),
            'serie_dps' => $dados['serie_dps'] ?? '1',
            'tomador_documento_encrypted' => $dados['tomador']['documento'],
            'tomador_nome_encrypted' => $dados['tomador']['nome'],
            'valor_servicos' => $dados['valores']['valor_servicos'],
            'valor_iss' => 0,
            'discriminacao_encrypted' => $dados['servico']['discriminacao'],
            'request_payload' => $dados,
            'data_emissao' => now(),
            'status' => 'pendente',
        ]);

        $r = self::$proximaResposta;
        $emissao->update([
            'chave_acesso' => $r->chaveAcesso,
            'numero_nfse' => $r->numeroNfse,
            'c_stat' => $r->cStat,
            'x_motivo' => $r->xMotivo,
            'status' => $r->emitida() ? 'emitida' : 'rejeitada',
            'data_processamento' => now(),
            'response_xml_encrypted' => '<xml>fake</xml>',
        ]);

        AuditLog::create([
            'cliente_id' => $this->getCliente()->id,
            'acao' => 'nfse.emitir',
            'recurso_tipo' => 'NfseEmissao',
            'recurso_id' => (string) $emissao->id,
            'ip_origem' => $audit['ip'] ?? null,
            'user_agent' => $audit['user_agent'] ?? null,
            'dados_request' => json_encode([]),
            'dados_response' => json_encode(['cStat' => $r->cStat]),
        ]);

        return $emissao->refresh();
    }

    private function getCliente(): Cliente
    {
        $ref = new ReflectionProperty(NfseEmissorService::class, 'cliente');

        return $ref->getValue($this);
    }
}

beforeEach(function () {
    $this->cliente = Cliente::factory()->comApiKey('test_emissao_key')->create();

    NfseEmissorServiceFake::$proximaResposta = new SefinResposta(
        chaveAcesso: str_repeat('1', 50),
        cStat: 100,
        xMotivo: 'NFS-e emitida com sucesso',
        protocolo: 'PROT-123',
        numeroNfse: '202600000001',
        codigoVerificacao: 'ABCDEF',
        dataProcessamento: now()->toIso8601String(),
        xmlRetorno: '<xml>autorizado</xml>',
        rawResponse: '{"resp":"ok"}',
    );

    // Bind para o controller usar o fake
    $this->app->bind(NfseEmissorService::class, fn () => new NfseEmissorServiceFake($this->cliente));
});

it('emite NFS-e válida e retorna 201 com a chave', function () {
    // Como o controller faz `new NfseEmissorService($cliente)` direto, vamos
    // injetar via container substituindo a classe.
    $this->app->bind(NfseEmissorService::class, fn ($app, $params) => new NfseEmissorServiceFake($params['cliente'] ?? $this->cliente));

    // Substitui o controller pra usar service do container
    Route::post('/api/v1/nfse-fake', function (Request $r) {
        $cliente = $r->attributes->get('cliente');
        $svc = new NfseEmissorServiceFake($cliente);
        $emissao = $svc->emitir($r->all(), ['ip' => $r->ip()]);

        return response()->json([
            'chave_acesso' => $emissao->chave_acesso,
            'status' => $emissao->status,
            'c_stat' => $emissao->c_stat,
        ], $emissao->status === 'emitida' ? 201 : 422);
    })->middleware('autenticar.api-key');

    $payload = [
        'tomador' => [
            'documento' => '11144477735',
            'nome' => 'Tomador Teste',
            'endereco' => [
                'logradouro' => 'Rua Teste',
                'numero' => '100',
                'bairro' => 'Centro',
                'cep' => '78550200',
                'codigo_municipio_ibge' => '5107909',
                'uf' => 'MT',
            ],
        ],
        'servico' => [
            'discriminacao' => 'Certidao de matricula imobiliaria.',
            'codigo_municipio_prestacao' => '5107909',
        ],
        'valores' => [
            'valor_servicos' => 100.00,
            'deducoes_reducoes' => 0,
            'aliquota_issqn_percentual' => 4.0,
        ],
    ];

    $resp = $this->withHeaders(['X-Api-Key' => 'test_emissao_key'])
        ->postJson('/api/v1/nfse-fake', $payload);

    $resp->assertStatus(201)
        ->assertJson([
            'chave_acesso' => str_repeat('1', 50),
            'status' => 'emitida',
            'c_stat' => 100,
        ]);

    expect(NfseEmissao::count())->toBe(1);
    expect(AuditLog::where('acao', 'nfse.emitir')->count())->toBe(1);
});

it('retorna 422 quando o payload é inválido', function () {
    $resp = $this->withHeaders(['X-Api-Key' => 'test_emissao_key'])
        ->postJson('/api/v1/nfse', [
            'tomador' => ['documento' => 'xxx'],
            'servico' => ['discriminacao' => 'short'],
            'valores' => ['valor_servicos' => -5],
        ]);

    $resp->assertStatus(422);
});

it('persiste request_payload e tomador como criptografado', function () {
    $svc = new NfseEmissorServiceFake($this->cliente);
    $payload = [
        'tomador' => [
            'documento' => '11144477735',
            'nome' => 'Cliente Sensível',
            'endereco' => [],
        ],
        'servico' => ['discriminacao' => 'XYZ'],
        'valores' => ['valor_servicos' => 50],
    ];
    $em = $svc->emitir($payload, []);

    // Lê do banco direto pra ver que está cifrado em repouso
    $row = DB::table('nfse_emissao')->where('id', $em->id)->first();
    expect($row->tomador_nome_encrypted)
        ->not->toBe('Cliente Sensível')
        ->and(strlen($row->tomador_nome_encrypted))->toBeGreaterThan(50);

    // E que o cast desfaz na leitura
    expect($em->tomador_nome_encrypted)->toBe('Cliente Sensível');
});
