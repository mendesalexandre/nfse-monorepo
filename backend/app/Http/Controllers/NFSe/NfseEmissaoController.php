<?php

namespace App\Http\Controllers\NFSe;

use App\Http\Controllers\Controller;
use App\Http\Requests\NFSe\CancelarNfseRequest;
use App\Http\Requests\NFSe\EmitirNfseRequest;
use App\Http\Resources\NFSe\NfseEmissaoResource;
use App\Models\Cliente;
use App\Services\NFSe\NfseEmissorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use PhpNfseNacional\Exceptions\CertificateException;
use PhpNfseNacional\Exceptions\SefinException;
use PhpNfseNacional\Exceptions\ValidationException;

class NfseEmissaoController extends Controller
{
    public function emitir(EmitirNfseRequest $request): JsonResponse
    {
        $cliente = $this->cliente($request);
        $service = new NfseEmissorService($cliente);

        try {
            $emissao = $service->emitir($request->validated(), [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'request' => $request->safe()->except(['valores', 'tomador.documento']),
            ]);
        } catch (SefinException $e) {
            return response()->json([
                'message' => 'SEFIN rejeitou a emissão.',
                'erro' => 'SefinException',
                'c_stat' => $e->cStat,
                'x_motivo' => $e->xMotivo,
            ], 422);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Dados inválidos para emissão.',
                'erro' => 'ValidationException',
                'detalhes' => method_exists($e, 'errors') ? $e->errors() : $e->getMessage(),
            ], 422);
        } catch (CertificateException $e) {
            return response()->json([
                'message' => 'Falha no certificado digital.',
                'erro' => 'CertificateException',
                'detalhes' => $e->getMessage(),
            ], 500);
        }

        return (new NfseEmissaoResource($emissao))
            ->response()
            ->setStatusCode($emissao->status === 'emitida' ? 201 : 422);
    }

    public function consultar(Request $request, string $chave): JsonResponse
    {
        $cliente = $this->cliente($request);
        $service = new NfseEmissorService($cliente);

        try {
            $resultado = $service->consultar($chave, [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Falha ao consultar SEFIN.',
                'detalhes' => $e->getMessage(),
            ], 502);
        }

        return response()->json($resultado);
    }

    public function cancelar(CancelarNfseRequest $request, string $chave): JsonResponse
    {
        $cliente = $this->cliente($request);
        $service = new NfseEmissorService($cliente);

        try {
            $emissao = $service->cancelar(
                $chave,
                (int) $request->validated('motivo'),
                (string) $request->validated('justificativa'),
                [
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'request' => $request->validated(),
                ],
            );
        } catch (SefinException $e) {
            return response()->json([
                'message' => 'SEFIN rejeitou o cancelamento.',
                'c_stat' => $e->cStat,
                'x_motivo' => $e->xMotivo,
            ], 422);
        }

        return (new NfseEmissaoResource($emissao))->response();
    }

    private function cliente(Request $request): Cliente
    {
        return $request->attributes->get('cliente');
    }
}
