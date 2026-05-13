<?php

namespace App\Services\NFSe;

use App\Models\AuditLog;
use App\Models\Cliente;
use App\Models\NfseEmissao;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use PhpNfseNacional\Config;
use PhpNfseNacional\DTO\Endereco;
use PhpNfseNacional\DTO\Identificacao;
use PhpNfseNacional\DTO\MotivoCancelamento;
use PhpNfseNacional\DTO\Prestador;
use PhpNfseNacional\DTO\Servico;
use PhpNfseNacional\DTO\Tomador;
use PhpNfseNacional\DTO\Valores;
use PhpNfseNacional\Enums\Ambiente;
use PhpNfseNacional\Enums\RegimeEspecialTributacao;
use PhpNfseNacional\Enums\SituacaoSimplesNacional;
use PhpNfseNacional\Exceptions\CertificateException;
use PhpNfseNacional\Exceptions\SefinException;
use PhpNfseNacional\Exceptions\ValidationException;
use PhpNfseNacional\NFSe;
use Throwable;

/**
 * Service de domínio que adapta o SDK público pra o nosso modelo Cliente.
 *
 * Responsabilidades:
 *   - Montar Config + Certificate a partir do Cliente
 *   - Mapear payload "nu" do request HTTP nos DTOs do SDK
 *   - Persistir o resultado em `nfse_emissao`
 *   - Logar auditoria LGPD em `audit_log`
 *
 * NÃO trata transporte/HTTP — o SDK cuida disso. NÃO retenta — quem chama
 * decide se quer reemitir.
 */
class NfseEmissorService
{
    public function __construct(
        private readonly Cliente $cliente,
        private readonly CertificadoLoader $certificadoLoader = new CertificadoLoader,
    ) {}

    /**
     * Emite uma NFS-e a partir do payload do request.
     *
     * @param  array  $dados  Estrutura validada por EmitirNfseRequest
     * @param  array  $audit  Contexto p/ audit log (ip, user_agent)
     */
    public function emitir(array $dados, array $audit = []): NfseEmissao
    {
        $facade = $this->montarFacade();

        // Numero DPS — se não veio, gera "AANNNNNN" (ano + 6 dígitos sequenciais por cliente)
        $numeroDps = (int) ($dados['numero_dps'] ?? $this->proximoNumeroDps());
        $serieDps = (string) ($dados['serie_dps'] ?? '1');

        $identificacao = new Identificacao(
            numeroDps: $numeroDps,
            serie: $serieDps,
            dataCompetencia: $this->parseDataOpcional($dados['data_emissao_retroativa'] ?? null),
            dataEmissao: $this->parseDataOpcional($dados['data_emissao_retroativa'] ?? null),
        );

        $tomador = $this->montarTomador($dados['tomador']);
        $servico = $this->montarServico($dados['servico']);
        $valores = $this->montarValores($dados['valores']);

        // Pré-grava como pendente pra ter id antes do envio
        $emissao = NfseEmissao::create([
            'cliente_id' => $this->cliente->id,
            'numero_dps' => $numeroDps,
            'serie_dps' => $serieDps,
            'tomador_documento_encrypted' => $tomador->documento,
            'tomador_nome_encrypted' => $tomador->nome,
            'valor_servicos' => $valores->valorServicos,
            'valor_iss' => $valores->valorIssqn(),
            'discriminacao_encrypted' => $servico->discriminacao,
            'request_payload' => $dados,
            'data_emissao' => now(),
            'status' => 'pendente',
        ]);

        try {
            $resposta = $facade->emitir($identificacao, $tomador, $servico, $valores);

            $emissao->fill([
                'chave_acesso' => $resposta->chaveAcesso,
                'numero_nfse' => $resposta->numeroNfse,
                'c_stat' => $resposta->cStat,
                'x_motivo' => $resposta->xMotivo,
                'response_xml_encrypted' => $resposta->xmlRetorno ?? $resposta->rawResponse,
                'data_processamento' => $resposta->dataProcessamento
                    ? Carbon::parse($resposta->dataProcessamento)
                    : now(),
                'status' => $resposta->emitida() ? 'emitida' : 'rejeitada',
            ])->save();

            $this->logAudit('nfse.emitir', $emissao, $audit, [
                'sucesso' => true,
                'cStat' => $resposta->cStat,
                'chave' => $resposta->chaveAcesso,
            ]);

            return $emissao->refresh();
        } catch (SefinException $e) {
            $emissao->fill([
                'c_stat' => $e->cStat,
                'x_motivo' => $e->xMotivo,
                'response_xml_encrypted' => $e->rawResponse,
                'status' => 'rejeitada',
            ])->save();

            $this->logAudit('nfse.emitir', $emissao, $audit, [
                'sucesso' => false,
                'erro' => 'SefinException',
                'cStat' => $e->cStat,
                'xMotivo' => $e->xMotivo,
            ]);

            throw $e;
        } catch (ValidationException|CertificateException $e) {
            $emissao->fill([
                'x_motivo' => $e->getMessage(),
                'status' => 'erro',
            ])->save();

            $this->logAudit('nfse.emitir', $emissao, $audit, [
                'sucesso' => false,
                'erro' => $e::class,
                'mensagem' => $e->getMessage(),
            ]);

            throw $e;
        } catch (Throwable $e) {
            $emissao->fill([
                'x_motivo' => $e->getMessage(),
                'status' => 'erro',
            ])->save();

            Log::channel(config('logging.default'))->error('[NfseEmissorService] erro emissão', [
                'cliente_id' => $this->cliente->id,
                'emissao_id' => $emissao->id,
                'erro' => $e::class,
                'mensagem' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Consulta status da NFS-e direto no SEFIN. Atualiza o registro local se
     * encontrar diferença (ex: NFSe cancelada por fora).
     */
    public function consultar(string $chave, array $audit = []): array
    {
        $facade = $this->montarFacade();
        $resposta = $facade->consultar($chave);

        $emissao = NfseEmissao::where('chave_acesso', $chave)
            ->where('cliente_id', $this->cliente->id)
            ->first();

        if ($emissao && $resposta->cancelada() && $emissao->status !== 'cancelada') {
            $emissao->update(['status' => 'cancelada', 'c_stat' => $resposta->cStat]);
        }

        $this->logAudit('nfse.consultar', $emissao, $audit, [
            'chave' => $chave,
            'cStat' => $resposta->cStat,
        ]);

        return [
            'chave_acesso' => $chave,
            'c_stat' => $resposta->cStat,
            'x_motivo' => $resposta->xMotivo,
            'numero_nfse' => $resposta->numeroNfse,
            'data_processamento' => $resposta->dataProcessamento,
            'cancelada' => $resposta->cancelada(),
            'emitida' => $resposta->emitida(),
            'status_local' => $emissao?->status,
        ];
    }

    /**
     * Cancela uma NFS-e via evento 101101.
     */
    public function cancelar(string $chave, int $motivoCodigo, string $justificativa, array $audit = []): NfseEmissao
    {
        $facade = $this->montarFacade();
        $motivo = MotivoCancelamento::from($motivoCodigo);

        $emissao = NfseEmissao::where('chave_acesso', $chave)
            ->where('cliente_id', $this->cliente->id)
            ->firstOrFail();

        try {
            $resposta = $facade->cancelar($chave, $motivo, $justificativa);

            $emissao->update([
                'status' => 'cancelada',
                'c_stat' => $resposta->cStat,
                'x_motivo' => $resposta->xMotivo,
            ]);

            $this->logAudit('nfse.cancelar', $emissao, $audit, [
                'sucesso' => true,
                'cStat' => $resposta->cStat,
                'motivo' => $motivo->label(),
                'justificativa' => $justificativa,
            ]);

            return $emissao->refresh();
        } catch (SefinException $e) {
            $this->logAudit('nfse.cancelar', $emissao, $audit, [
                'sucesso' => false,
                'cStat' => $e->cStat,
                'xMotivo' => $e->xMotivo,
            ]);
            throw $e;
        }
    }

    /**
     * Gera o DANFSe (PDF) localmente a partir do XML autorizado.
     */
    public function gerarDanfsePdf(string $chave): string
    {
        $facade = $this->montarFacade();

        $emissao = NfseEmissao::where('chave_acesso', $chave)
            ->where('cliente_id', $this->cliente->id)
            ->firstOrFail();

        $xml = $emissao->response_xml_encrypted;
        if (empty($xml)) {
            // Fallback: baixar do ADN
            $xml = $facade->baixarXml($chave);
        }

        return $facade->danfseLocal($xml);
    }

    // ===== Internals =====

    public function montarFacade(): NFSe
    {
        $cert = $this->certificadoLoader->carregar($this->cliente);

        $endereco = new Endereco(
            logradouro: $this->cliente->logradouro,
            numero: $this->cliente->numero,
            bairro: $this->cliente->bairro,
            cep: preg_replace('/\D/', '', $this->cliente->cep) ?? '',
            codigoMunicipioIbge: $this->cliente->codigo_municipio_ibge,
            uf: strtoupper($this->cliente->uf),
            complemento: $this->cliente->complemento,
        );

        $prestador = new Prestador(
            cnpj: $this->cliente->cnpj,
            inscricaoMunicipal: $this->cliente->inscricao_municipal,
            razaoSocial: $this->cliente->razao_social_prestador,
            endereco: $endereco,
            regimeEspecial: RegimeEspecialTributacao::from((int) $this->cliente->regime_especial_tributacao),
            simplesNacional: SituacaoSimplesNacional::from((int) $this->cliente->simples_nacional),
            email: $this->cliente->email,
            telefone: $this->cliente->telefone,
        );

        $config = new Config(
            prestador: $prestador,
            ambiente: $this->cliente->ambiente === 'producao' ? Ambiente::Producao : Ambiente::Homologacao,
            incluirIbsCbs: (bool) $this->cliente->incluir_ibscbs,
        );

        // Logger PSR-3 padrão do Laravel (canal `nfse` se configurado)
        $logger = config('logging.channels.nfse')
            ? Log::channel('nfse')
            : Log::channel(config('logging.default'));

        return NFSe::create($config, $cert, http: null, logger: $logger);
    }

    private function montarTomador(array $t): Tomador
    {
        $end = $t['endereco'];

        return new Tomador(
            documento: (string) $t['documento'],
            nome: (string) $t['nome'],
            endereco: new Endereco(
                logradouro: (string) $end['logradouro'],
                numero: (string) $end['numero'],
                bairro: (string) $end['bairro'],
                cep: preg_replace('/\D/', '', (string) $end['cep']) ?? '',
                codigoMunicipioIbge: (string) $end['codigo_municipio_ibge'],
                uf: strtoupper((string) $end['uf']),
                complemento: $end['complemento'] ?? null,
            ),
            email: $t['email'] ?? null,
            telefone: $t['telefone'] ?? null,
            inscricaoMunicipal: $t['inscricao_municipal'] ?? null,
        );
    }

    private function montarServico(array $s): Servico
    {
        return new Servico(
            discriminacao: (string) $s['discriminacao'],
            codigoMunicipioPrestacao: (string) ($s['codigo_municipio_prestacao'] ?? $this->cliente->codigo_municipio_ibge),
            cTribNac: (string) ($s['c_trib_nac'] ?? '210101'),
            cNBS: (string) ($s['c_nbs'] ?? '113040000'),
        );
    }

    private function montarValores(array $v): Valores
    {
        return new Valores(
            valorServicos: (float) $v['valor_servicos'],
            deducoesReducoes: (float) ($v['deducoes_reducoes'] ?? 0),
            aliquotaIssqnPercentual: (float) $v['aliquota_issqn_percentual'],
            issqnRetido: (bool) ($v['issqn_retido'] ?? false),
            descontoIncondicionado: (float) ($v['desconto_incondicionado'] ?? 0),
        );
    }

    private function parseDataOpcional(?string $iso): ?\DateTimeImmutable
    {
        if ($iso === null || $iso === '') {
            return null;
        }

        return new \DateTimeImmutable($iso);
    }

    /**
     * Próximo nDPS pra esse cliente — esquema AANNNNNN. Tenta MAX local + 1;
     * em homologação fallback adiciona timestamp na ponta pra evitar colisão
     * com NFS-es de runs anteriores que já estão registradas no SEFIN.
     */
    private function proximoNumeroDps(): int
    {
        $ano = (int) date('y');
        $base = $ano * 1_000_000;

        $ultimo = (int) NfseEmissao::where('cliente_id', $this->cliente->id)
            ->where('numero_dps', '>=', $base)
            ->where('numero_dps', '<', $base + 1_000_000)
            ->max('numero_dps');

        // Em homologação, usa nDPS aleatório dentro da faixa do ano pra evitar
        // colisão entre re-execuções do smoke test (banco zerado mas SEFIN guarda
        // histórico de nDPS já usados pelo CNPJ/IM/Mun).
        if ($this->cliente->ambiente !== 'producao') {
            // Tenta até 5 vezes encontrar um nDPS livre no banco local
            for ($i = 0; $i < 5; $i++) {
                $candidato = $base + random_int(1, 999_999);
                $existe = NfseEmissao::where('cliente_id', $this->cliente->id)
                    ->where('numero_dps', $candidato)
                    ->where('serie_dps', '1')
                    ->exists();
                if (! $existe) {
                    return $candidato;
                }
            }

            // Fallback determinístico
            return $ultimo > 0 ? $ultimo + 1 : $base + 1;
        }

        return $ultimo > 0 ? $ultimo + 1 : $base + 1;
    }

    private function logAudit(string $acao, ?NfseEmissao $emissao, array $audit, array $extra): void
    {
        try {
            AuditLog::create([
                'cliente_id' => $this->cliente->id,
                'acao' => $acao,
                'recurso_tipo' => $emissao ? 'NfseEmissao' : null,
                'recurso_id' => $emissao?->id ? (string) $emissao->id : null,
                'ip_origem' => $audit['ip'] ?? null,
                'user_agent' => $audit['user_agent'] ?? null,
                'dados_request' => json_encode($audit['request'] ?? null),
                'dados_response' => json_encode($extra),
            ]);
        } catch (Throwable $e) {
            // Audit nunca pode quebrar o request. Loga e segue.
            Log::warning('[NfseEmissorService] falha ao gravar audit', [
                'erro' => $e->getMessage(),
            ]);
        }
    }
}
