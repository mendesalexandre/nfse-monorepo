<?php

namespace App\Console\Commands;

use App\Models\Cliente;
use App\Services\NFSe\NfseEmissorService;
use Illuminate\Console\Command;
use PhpNfseNacional\DTO\MotivoCancelamento;
use PhpNfseNacional\DTO\MotivoSubstituicao;
use PhpNfseNacional\DTO\MotivoRejeicao;
use PhpNfseNacional\Enums\AutorManifestacao;
use PhpNfseNacional\Exceptions\SefinException;

/**
 * Roda uma bateria de cenários de emissão em homologação contra o SEFIN
 * Nacional usando o cert do cartório de Sinop.
 *
 * Cobertura:
 *  1. Tomador PF básico (CPF, sem email/telefone)
 *  2. Tomador PF com email + telefone
 *  3. Tomador PJ (CNPJ)
 *  4. Tomador PJ com IM
 *  5. Alíquota fracionada (3.5125 → 3.51)
 *  6. Alíquota fracionada arredonda pra cima (3.5995 → 3.60)
 *  7. IBSCBS habilitado (toggle on no cliente clonado)
 *  8. Emissão retroativa (-7 dias)
 *  9. Cancelar uma das emitidas (motivo 1, justificativa)
 * 10. Substituir uma (emite substituidora + chama substituicao)
 * 11. Manifestar Confirmação como Prestador
 * 12. Manifestar Rejeição como Prestador
 *
 * Uso:
 *   OPENSSL_CONF=/home/alexandre/code/SINOP/backend/docs/openssl-sha1.cnf \
 *     php artisan nfse:smoke-test
 */
class NfseSmokeTestCommand extends Command
{
    protected $signature = 'nfse:smoke-test
                            {--cliente=00179028000138 : CNPJ do cliente a usar}
                            {--cenarios= : CSV dos cenários a rodar (default: todos)}';

    protected $description = 'Bateria de smoke tests do SDK em homologação';

    public function handle(): int
    {
        $cliente = Cliente::where('cnpj', $this->option('cliente'))->first();
        if (! $cliente) {
            $this->error('Cliente não encontrado: '.$this->option('cliente'));

            return self::FAILURE;
        }

        if ($cliente->ambiente !== 'homologacao') {
            $this->error('Cliente não está em homologação. Abortando por segurança.');

            return self::FAILURE;
        }

        $svc = new NfseEmissorService($cliente);
        $resultados = [];

        $cenariosParam = $this->option('cenarios');
        $cenariosAtivos = $cenariosParam
            ? array_map('intval', explode(',', $cenariosParam))
            : range(1, 12);

        // Cenário 1: PF básico
        if (in_array(1, $cenariosAtivos)) {
            $resultados[1] = $this->emitir($svc, 'PF básico', [
                'tomador' => $this->tomadorPF([], false, false),
                'servico' => $this->servicoPadrao('Cenário 1 — Certidão de matrícula em PF, sem email.'),
                'valores' => $this->valoresPadrao(100.00, 4.00),
            ]);
        }

        // Cenário 2: PF com contato
        if (in_array(2, $cenariosAtivos)) {
            $resultados[2] = $this->emitir($svc, 'PF com email/telefone', [
                'tomador' => $this->tomadorPF([], true, true),
                'servico' => $this->servicoPadrao('Cenário 2 — PF com email e telefone informados.'),
                'valores' => $this->valoresPadrao(150.50, 4.00),
            ]);
        }

        // Cenário 3: PJ básico
        if (in_array(3, $cenariosAtivos)) {
            $resultados[3] = $this->emitir($svc, 'PJ básico', [
                'tomador' => $this->tomadorPJ([], false),
                'servico' => $this->servicoPadrao('Cenário 3 — Certidão de matrícula a PJ tomadora.'),
                'valores' => $this->valoresPadrao(225.00, 4.00),
            ]);
        }

        // Cenário 4: PJ com IM
        if (in_array(4, $cenariosAtivos)) {
            $resultados[4] = $this->emitir($svc, 'PJ com IM', [
                'tomador' => $this->tomadorPJ(['inscricao_municipal' => '99887'], true),
                'servico' => $this->servicoPadrao('Cenário 4 — PJ tomadora com inscrição municipal informada.'),
                'valores' => $this->valoresPadrao(300.00, 4.00),
            ]);
        }

        // Cenário 5: alíquota fracionada (round half-up)
        if (in_array(5, $cenariosAtivos)) {
            $resultados[5] = $this->emitir($svc, 'Alíquota 3.5125 (→ 3.51)', [
                'tomador' => $this->tomadorPF([], false, false),
                'servico' => $this->servicoPadrao('Cenário 5 — Alíquota fracionada arredondando pra baixo.'),
                'valores' => $this->valoresPadrao(180.00, 3.5125),
            ]);
        }

        // Cenário 6: alíquota fracionada arredonda pra cima
        if (in_array(6, $cenariosAtivos)) {
            $resultados[6] = $this->emitir($svc, 'Alíquota 3.5995 (→ 3.60)', [
                'tomador' => $this->tomadorPF([], false, false),
                'servico' => $this->servicoPadrao('Cenário 6 — Alíquota fracionada arredondando pra cima.'),
                'valores' => $this->valoresPadrao(180.00, 3.5995),
            ]);
        }

        // Cenário 7: IBSCBS toggle on (cliente clonado em memória)
        if (in_array(7, $cenariosAtivos)) {
            $clienteIbsCbs = $cliente->replicate(['data_criacao', 'data_alteracao']);
            $clienteIbsCbs->incluir_ibscbs = true;
            // Nota: NÃO persistimos — só usamos pra montar o facade com flag on.
            $clienteIbsCbs->id = $cliente->id;
            $clienteIbsCbs->exists = true;
            $svcIbs = new NfseEmissorService($clienteIbsCbs);
            $resultados[7] = $this->emitir($svcIbs, 'IBSCBS on', [
                'tomador' => $this->tomadorPF([], false, false),
                'servico' => $this->servicoPadrao('Cenário 7 — Reforma Tributária: bloco IBSCBS no DPS.'),
                'valores' => $this->valoresPadrao(120.00, 4.00),
            ]);
        }

        // Cenário 8: emissão retroativa (-7 dias)
        if (in_array(8, $cenariosAtivos)) {
            $resultados[8] = $this->emitir($svc, 'Retroativo -7d', [
                'tomador' => $this->tomadorPF([], false, false),
                'servico' => $this->servicoPadrao('Cenário 8 — Emissão com data retroativa de 7 dias.'),
                'valores' => $this->valoresPadrao(95.00, 4.00),
                'data_emissao_retroativa' => now()->subDays(7)->setTimezone('America/Sao_Paulo')->format(DATE_ATOM),
            ]);
        }

        // Cenário 9: cancelar uma das emitidas (a primeira que tiver chave)
        if (in_array(9, $cenariosAtivos)) {
            $resultados[9] = $this->cancelarPrimeiraEmitida($svc, $resultados);
        }

        // Cenário 10: substituir — emite substitutiva + chama substituicao()
        if (in_array(10, $cenariosAtivos)) {
            $resultados[10] = $this->substituirPrimeiraEmitida($svc, $cliente, $resultados);
        }

        // Cenário 11: manifestar como Prestador (Confirmar)
        if (in_array(11, $cenariosAtivos)) {
            $resultados[11] = $this->manifestar(
                $svc, $cliente, $resultados,
                'Confirmação Prestador',
                fn ($facade, $chave) => $facade->confirmar($chave, AutorManifestacao::Prestador),
            );
        }

        // Cenário 12: manifestar Rejeição como Prestador
        if (in_array(12, $cenariosAtivos)) {
            $resultados[12] = $this->manifestar(
                $svc, $cliente, $resultados,
                'Rejeição Prestador',
                function ($facade, $chave) {
                    return $facade->rejeitar(
                        $chave,
                        AutorManifestacao::Prestador,
                        MotivoRejeicao::Outros,
                        'Smoke test - rejeicao manual cenario 12 via API.',
                    );
                },
            );
        }

        $this->renderResumo($resultados);

        return self::SUCCESS;
    }

    private function emitir(NfseEmissorService $svc, string $label, array $payload): array
    {
        $this->line("[{$label}] iniciando...");
        try {
            $emissao = $svc->emitir($payload, ['ip' => '127.0.0.1', 'user_agent' => 'smoke-test']);
            $msg = $emissao->status === 'emitida' ? '<info>OK</info>' : "<comment>{$emissao->status}</comment>";
            $this->line("  {$msg} cStat={$emissao->c_stat} chave=".($emissao->chave_acesso ?? '-'));

            return [
                'label' => $label,
                'ok' => $emissao->status === 'emitida',
                'chave' => $emissao->chave_acesso,
                'cStat' => $emissao->c_stat,
                'xMotivo' => $emissao->x_motivo,
                'numero_dps' => $emissao->numero_dps,
            ];
        } catch (SefinException $e) {
            $this->error("  FALHA SEFIN cStat={$e->cStat} {$e->xMotivo}");

            return ['label' => $label, 'ok' => false, 'cStat' => $e->cStat, 'xMotivo' => $e->xMotivo];
        } catch (\Throwable $e) {
            $this->error('  FALHA '.$e::class.': '.$e->getMessage());

            return ['label' => $label, 'ok' => false, 'erro' => $e->getMessage()];
        }
    }

    private function cancelarPrimeiraEmitida(NfseEmissorService $svc, array $resultados): array
    {
        foreach ($resultados as $r) {
            if (($r['ok'] ?? false) && ! empty($r['chave'])) {
                $this->line("[Cancelamento] tentando chave={$r['chave']}");
                try {
                    $emissao = $svc->cancelar($r['chave'], 1, 'Cancelamento smoke test cenario 9 - erro emissao via API.');
                    $this->line("  <info>OK</info> status={$emissao->status} cStat={$emissao->c_stat}");

                    return ['label' => 'Cancelamento', 'ok' => true, 'chave' => $r['chave'], 'cStat' => $emissao->c_stat];
                } catch (SefinException $e) {
                    $this->error("  FALHA SEFIN cStat={$e->cStat} {$e->xMotivo}");

                    return ['label' => 'Cancelamento', 'ok' => false, 'cStat' => $e->cStat, 'xMotivo' => $e->xMotivo];
                } catch (\Throwable $e) {
                    $this->error('  FALHA '.$e::class.': '.$e->getMessage());

                    return ['label' => 'Cancelamento', 'ok' => false, 'erro' => $e->getMessage()];
                }
            }
        }
        $this->warn('[Cancelamento] sem NFSe emitida pra cancelar.');

        return ['label' => 'Cancelamento', 'ok' => false, 'erro' => 'sem chave disponível'];
    }

    private function substituirPrimeiraEmitida(NfseEmissorService $svc, Cliente $cliente, array &$resultados): array
    {
        // Encontra uma chave emitida ainda não cancelada
        $chaveOriginal = null;
        foreach ($resultados as $i => $r) {
            if (($r['ok'] ?? false) && ! empty($r['chave']) && $i !== 9) {
                $chaveOriginal = $r['chave'];
                break;
            }
        }
        if (! $chaveOriginal) {
            $this->warn('[Substituicao] sem chave original disponível.');

            return ['label' => 'Substituicao', 'ok' => false, 'erro' => 'sem chave original'];
        }

        // Emite a substituidora primeiro
        $sub = $this->emitir($svc, 'Substituidora (pré-substituição)', [
            'tomador' => $this->tomadorPF([], false, false),
            'servico' => $this->servicoPadrao('Cenário 10 — Substituidora da NFSe original '.$chaveOriginal),
            'valores' => $this->valoresPadrao(110.00, 4.00),
        ]);
        if (! ($sub['ok'] ?? false) || empty($sub['chave'])) {
            return ['label' => 'Substituicao', 'ok' => false, 'erro' => 'falha ao emitir substituidora'];
        }

        // Chama substituicao() do facade direto
        try {
            $facade = $svc->montarFacade();
            // SDK v0.5.0: API achatada (sem ->substituicao()).
            // SDK v0.5.1: MotivoSubstituicao (não MotivoCancelamento) + xMotivo opcional.
            $resp = $facade->substituir(
                $chaveOriginal,
                $sub['chave'],
                MotivoSubstituicao::DesenquadramentoSimples,
            );
            $this->line("  <info>OK substituicao</info> cStat={$resp->cStat}");

            return [
                'label' => 'Substituicao',
                'ok' => true,
                'chave_original' => $chaveOriginal,
                'chave_sub' => $sub['chave'],
                'cStat' => $resp->cStat,
            ];
        } catch (SefinException $e) {
            $this->error("  FALHA SEFIN cStat={$e->cStat} {$e->xMotivo}");

            return ['label' => 'Substituicao', 'ok' => false, 'cStat' => $e->cStat, 'xMotivo' => $e->xMotivo];
        } catch (\Throwable $e) {
            $this->error('  FALHA '.$e::class.': '.$e->getMessage());

            return ['label' => 'Substituicao', 'ok' => false, 'erro' => $e->getMessage()];
        }
    }

    private function manifestar(NfseEmissorService $svc, Cliente $cliente, array $resultados, string $label, \Closure $fn): array
    {
        // Não pode reusar chave cancelada (cenário 9), substituída (10), ou já manifestada
        // (regra E1833: cada autor só pode emitir UMA manifestação por NFSe).
        $chaveCancelada = $resultados[9]['chave'] ?? null;
        $chaveSub = $resultados[10]['chave_original'] ?? null;
        $chaveManifestada = null;
        for ($i = 11; $i < 12; $i++) {
            if (($resultados[$i]['ok'] ?? false)) {
                $chaveManifestada = $resultados[$i]['chave'] ?? null;
            }
        }

        $chave = null;
        foreach ($resultados as $i => $r) {
            if (! ($r['ok'] ?? false) || empty($r['chave'])) {
                continue;
            }
            if (in_array($i, [9, 10], true)) {
                continue; // chave fonte de cancelamento/sub
            }
            $c = $r['chave'];
            if ($c === $chaveCancelada || $c === $chaveSub || $c === $chaveManifestada) {
                continue;
            }
            $chave = $c;
            break;
        }
        if (! $chave) {
            $this->warn("[{$label}] sem chave disponível.");

            return ['label' => $label, 'ok' => false, 'erro' => 'sem chave'];
        }

        try {
            $facade = $svc->montarFacade();
            $resp = $fn($facade, $chave);
            $this->line("[{$label}] <info>OK</info> chave={$chave} cStat={$resp->cStat}");

            return ['label' => $label, 'ok' => true, 'chave' => $chave, 'cStat' => $resp->cStat];
        } catch (SefinException $e) {
            $this->error("[{$label}] FALHA SEFIN cStat={$e->cStat} {$e->xMotivo}");

            return ['label' => $label, 'ok' => false, 'cStat' => $e->cStat, 'xMotivo' => $e->xMotivo];
        } catch (\Throwable $e) {
            $this->error("[{$label}] FALHA ".$e::class.': '.$e->getMessage());

            return ['label' => $label, 'ok' => false, 'erro' => $e->getMessage()];
        }
    }

    private function tomadorPF(array $extras, bool $email, bool $tel): array
    {
        return array_merge([
            'documento' => '11144477735',
            'nome' => 'Joao da Silva Smoke Test',
            'email' => $email ? 'joao.smoke@example.com' : null,
            'telefone' => $tel ? '6599991122' : null,
            'endereco' => [
                'logradouro' => 'Rua dos Testes',
                'numero' => '100',
                'bairro' => 'Centro',
                'cep' => '78550200',
                'codigo_municipio_ibge' => '5107909',
                'uf' => 'MT',
            ],
        ], $extras);
    }

    private function tomadorPJ(array $extras, bool $email): array
    {
        return array_merge([
            'documento' => '00000000000191', // CNPJ exemplo (BB)
            'nome' => 'Empresa Tomadora Smoke LTDA',
            'email' => $email ? 'fiscal@empresa-smoke.example.com' : null,
            'telefone' => null,
            'endereco' => [
                'logradouro' => 'Avenida Comercial',
                'numero' => '500',
                'bairro' => 'Setor Industrial',
                'cep' => '78550200',
                'codigo_municipio_ibge' => '5107909',
                'uf' => 'MT',
            ],
        ], $extras);
    }

    private function servicoPadrao(string $disc): array
    {
        return [
            'discriminacao' => $disc,
            'codigo_municipio_prestacao' => '5107909',
            'c_trib_nac' => '210101',
        ];
    }

    private function valoresPadrao(float $valor, float $aliquota): array
    {
        return [
            'valor_servicos' => $valor,
            'deducoes_reducoes' => 0,
            'aliquota_issqn_percentual' => $aliquota,
        ];
    }

    private function renderResumo(array $resultados): void
    {
        $this->line('');
        $this->line('=== Resumo smoke test ===');

        $rows = [];
        foreach ($resultados as $i => $r) {
            $rows[] = [
                $i,
                $r['label'] ?? '-',
                ($r['ok'] ?? false) ? 'OK' : 'FAIL',
                $r['chave'] ?? '-',
                (string) ($r['cStat'] ?? '-'),
                substr((string) ($r['xMotivo'] ?? $r['erro'] ?? ''), 0, 60),
            ];
        }
        $this->table(['#', 'Cenário', 'Status', 'Chave', 'cStat', 'Mensagem'], $rows);
    }
}
