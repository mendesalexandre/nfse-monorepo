<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\NFSe\DanfseController;
use App\Http\Controllers\NFSe\NfseEmissaoController;
use Illuminate\Support\Facades\Route;

// Gerar token (não precisa estar autenticado) — auth de USUÁRIO via SPA/painel
Route::post('/tokens', [AuthController::class, 'createToken']);

// Rotas protegidas por sessão SPA / Bearer token (Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);

    // Gerenciamento de tokens
    Route::get('/tokens', [AuthController::class, 'listTokens']);
    Route::delete('/tokens/current', [AuthController::class, 'revokeCurrentToken']);
    Route::delete('/tokens/{id}', [AuthController::class, 'revokeToken']);
});

// API multi-tenant de NFS-e (auth por X-Api-Key do cliente)
Route::prefix('v1')->middleware(['autenticar.api-key'])->group(function () {
    Route::post('/nfse', [NfseEmissaoController::class, 'emitir']);
    Route::get('/nfse/{chave}', [NfseEmissaoController::class, 'consultar'])
        ->where('chave', '[0-9]{50}');
    Route::post('/nfse/{chave}/cancelar', [NfseEmissaoController::class, 'cancelar'])
        ->where('chave', '[0-9]{50}');
    Route::get('/danfse/{chave}', [DanfseController::class, 'baixar'])
        ->where('chave', '[0-9]{50}');
});
