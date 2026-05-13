<?php

namespace App\Http\Controllers\NFSe;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Services\NFSe\NfseEmissorService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DanfseController extends Controller
{
    public function baixar(Request $request, string $chave): Response
    {
        /** @var Cliente $cliente */
        $cliente = $request->attributes->get('cliente');
        $service = new NfseEmissorService($cliente);

        try {
            $pdf = $service->gerarDanfsePdf($chave);
        } catch (\Throwable $e) {
            return response($e->getMessage(), 500);
        }

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="danfse-'.$chave.'.pdf"',
        ]);
    }
}
