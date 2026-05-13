<?php

use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\ClienteController as AdminClienteController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\NfseEmissaoController as AdminNfseEmissaoController;
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

    // ===== Painel admin =====
    Route::prefix('admin')->group(function (): void {
        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index']);

        // Clientes (CRUD + credenciais + cert)
        Route::get('/clientes', [AdminClienteController::class, 'index'])
            ->middleware('permissao:cliente.criar,cliente.editar,nfse.consultar');
        Route::post('/clientes', [AdminClienteController::class, 'store'])
            ->middleware('permissao:cliente.criar');
        Route::get('/clientes/{cliente}', [AdminClienteController::class, 'show'])
            ->middleware('permissao:cliente.editar,nfse.consultar');
        Route::put('/clientes/{cliente}', [AdminClienteController::class, 'update'])
            ->middleware('permissao:cliente.editar');
        Route::delete('/clientes/{cliente}', [AdminClienteController::class, 'destroy'])
            ->middleware('permissao:cliente.editar');

        Route::post('/clientes/{cliente}/cert', [AdminClienteController::class, 'uploadCert'])
            ->middleware('permissao:cliente.editar');
        Route::post('/clientes/{cliente}/regenerar-api-key', [AdminClienteController::class, 'regenerarApiKey'])
            ->middleware('permissao:cliente.gerar_credenciais');
        Route::post('/clientes/{cliente}/regenerar-client-secret', [AdminClienteController::class, 'regenerarClientSecret'])
            ->middleware('permissao:cliente.gerar_credenciais');
        Route::post('/clientes/{cliente}/revogar', [AdminClienteController::class, 'revogar'])
            ->middleware('permissao:cliente.revogar');

        // NFS-es (admin view-only)
        Route::get('/nfses', [AdminNfseEmissaoController::class, 'index'])
            ->middleware('permissao:nfse.consultar');
        Route::get('/nfses/{nfse}', [AdminNfseEmissaoController::class, 'show'])
            ->middleware('permissao:nfse.consultar');
        Route::get('/nfses/{nfse}/pdf', [AdminNfseEmissaoController::class, 'baixarPdf'])
            ->middleware('permissao:nfse.consultar');

        // Audit logs
        Route::get('/audit-logs', [AuditLogController::class, 'index'])
            ->middleware('permissao:nfse.consultar');
    });
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
