<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SalvarClienteRequest;
use App\Http\Requests\Admin\UploadCertificadoRequest;
use App\Http\Resources\Admin\ClienteResource;
use App\Models\Cliente;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use PhpNfseNacional\Certificate\Certificate;
use PhpNfseNacional\Exceptions\CertificateException;

/**
 * CRUD admin de clientes da API multi-tenant.
 *
 * Operações sensíveis (cert, credenciais) ficam em endpoints dedicados pra
 * facilitar auditoria e nunca aceitar esses campos via update geral.
 */
class ClienteController extends Controller
{
    /**
     * GET /api/admin/clientes
     *
     * Lista paginada (20/pg). Filtros: `busca` (nome/cnpj), `is_ativo`.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Cliente::query()->orderByDesc('data_criacao');

        if ($busca = trim((string) $request->query('busca'))) {
            $cnpjLimpo = preg_replace('/\D/', '', $busca);
            $query->where(function ($q) use ($busca, $cnpjLimpo) {
                $q->where('nome_empresa', 'like', "%{$busca}%")
                    ->orWhere('email', 'like', "%{$busca}%");
                if ($cnpjLimpo !== '') {
                    $q->orWhere('cnpj', 'like', "%{$cnpjLimpo}%");
                }
            });
        }

        if ($request->has('is_ativo') && $request->query('is_ativo') !== '' && $request->query('is_ativo') !== null) {
            $query->where('is_ativo', filter_var($request->query('is_ativo'), FILTER_VALIDATE_BOOLEAN));
        }

        $perPage = (int) min(100, max(5, (int) $request->query('per_page', 20)));
        $paginated = $query->paginate($perPage);

        return ClienteResource::collection($paginated)->response();
    }

    /**
     * POST /api/admin/clientes
     *
     * Cria cliente já com credenciais geradas (api_key + client_id + client_secret).
     * Cert é zero — precisa subir depois via /upload-cert. Cliente fica `is_ativo=false`
     * efetivo até ter cert (o middleware aceita mas a emissão falha em CertificateException).
     *
     * Retorna as credenciais planas UMA vez no body — frontend precisa exibir
     * num modal copy-once.
     */
    public function store(SalvarClienteRequest $request): JsonResponse
    {
        $apiKey = Cliente::gerarApiKey();
        $clientId = Cliente::gerarClientId();
        $clientSecret = Cliente::gerarClientSecret();

        $cliente = Cliente::create(array_merge($request->validated(), [
            'api_key_hash' => Hash::make($apiKey),
            'client_id' => $clientId,
            'client_secret_hash' => Hash::make($clientSecret),

            // Sem cert ainda — campos obrigatórios na migration, então temporariamente vazios
            'pfx_encrypted' => '',
            'pfx_senha_encrypted' => '',
            'cert_validade' => now()->subDay()->format('Y-m-d'),
            'cert_cnpj' => $request->validated()['cnpj'],
        ]));

        return response()->json([
            'cliente' => new ClienteResource($cliente),
            'credenciais' => [
                'api_key' => $apiKey,
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'aviso' => 'Estas credenciais NÃO serão exibidas novamente. Copie agora.',
            ],
        ], 201);
    }

    /**
     * GET /api/admin/clientes/{cliente}
     */
    public function show(Cliente $cliente): JsonResponse
    {
        return (new ClienteResource($cliente))->response();
    }

    /**
     * PUT /api/admin/clientes/{cliente}
     */
    public function update(SalvarClienteRequest $request, Cliente $cliente): JsonResponse
    {
        $cliente->update($request->validated());
        $this->invalidarCache();

        return (new ClienteResource($cliente->refresh()))->response();
    }

    /**
     * DELETE /api/admin/clientes/{cliente}
     *
     * Soft delete via SoftDeletes (data_exclusao). Não revoga credenciais por si só
     * — pra zerar uso, chame /revogar antes (ou só faça o delete que o middleware
     * já filtra por is_ativo + soft delete).
     */
    public function destroy(Cliente $cliente): JsonResponse
    {
        $cliente->delete();
        $this->invalidarCache();

        return response()->json(['message' => 'Cliente removido.']);
    }

    /**
     * POST /api/admin/clientes/{cliente}/cert
     *
     * Upload do PFX + senha. Tenta abrir antes de persistir — se a senha estiver
     * errada ou o PFX corrompido, devolve 422 com a mensagem do SDK.
     */
    public function uploadCert(UploadCertificadoRequest $request, Cliente $cliente): JsonResponse
    {
        $arquivo = $request->file('pfx');
        $pfxBinario = file_get_contents($arquivo->getRealPath());

        if ($pfxBinario === false || $pfxBinario === '') {
            return response()->json([
                'message' => 'Falha ao ler o arquivo PFX.',
            ], 422);
        }

        $senha = (string) $request->validated('senha');

        // Valida o cert antes de gravar — qualquer erro vira 422 com a mensagem do SDK
        try {
            $cert = Certificate::fromPfxContent($pfxBinario, $senha);
        } catch (CertificateException $e) {
            return response()->json([
                'message' => 'Certificado inválido: '.$e->getMessage(),
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Falha ao processar certificado: '.$e->getMessage(),
            ], 422);
        }

        $cliente->update([
            'pfx_encrypted' => base64_encode($pfxBinario), // será re-cifrado pelo cast 'encrypted'
            'pfx_senha_encrypted' => $senha,
            'cert_validade' => $cert->validade->format('Y-m-d'),
            'cert_cnpj' => $cert->cnpj ?: $cliente->cnpj,
        ]);
        $this->invalidarCache();

        return response()->json([
            'cliente' => new ClienteResource($cliente->refresh()),
            'cert_validade' => $cert->validade->format('Y-m-d'),
            'cert_cnpj' => $cert->cnpj,
        ]);
    }

    /**
     * POST /api/admin/clientes/{cliente}/regenerar-api-key
     *
     * Gera nova api key plana + hash bcrypt. A nova chave é entregue UMA vez
     * (frontend precisa de modal copy-once).
     */
    public function regenerarApiKey(Cliente $cliente): JsonResponse
    {
        $apiKey = Cliente::gerarApiKey();
        $cliente->update(['api_key_hash' => Hash::make($apiKey)]);
        $this->invalidarCache();

        return response()->json([
            'api_key' => $apiKey,
            'aviso' => 'Esta credencial NÃO será exibida novamente. Copie agora.',
        ]);
    }

    /**
     * POST /api/admin/clientes/{cliente}/regenerar-client-secret
     */
    public function regenerarClientSecret(Cliente $cliente): JsonResponse
    {
        $clientSecret = Cliente::gerarClientSecret();
        $cliente->update(['client_secret_hash' => Hash::make($clientSecret)]);
        $this->invalidarCache();

        return response()->json([
            'client_secret' => $clientSecret,
            'aviso' => 'Esta credencial NÃO será exibida novamente. Copie agora.',
        ]);
    }

    /**
     * POST /api/admin/clientes/{cliente}/revogar
     *
     * Zera api_key_hash e client_secret_hash. Cliente vira inutilizável até
     * receber novas credenciais. NÃO desativa (use update is_ativo=false pra
     * isso) porque a operação é independente.
     */
    public function revogar(Cliente $cliente): JsonResponse
    {
        $cliente->update([
            'api_key_hash' => '',
            'client_secret_hash' => '',
        ]);
        $this->invalidarCache();

        return response()->json([
            'message' => 'Credenciais revogadas. Cliente está inutilizável até novas credenciais serem geradas.',
        ]);
    }

    /**
     * Limpa o cache de resolução de api key — usado em cada mutação que altera
     * `api_key_hash` ou `is_ativo`.
     *
     * Como o cache é por hash(plain) e não temos a plain, fazemos flush mais
     * simples: limpa as entradas conhecidas. Pra escala maior, trocar pra cache
     * tag.
     */
    private function invalidarCache(): void
    {
        // Estratégia simples: o middleware tem cache curto (60s), só precisa
        // não persistir a invalidação. Em produção real, usar tags do Redis.
        Cache::flush();
    }
}
