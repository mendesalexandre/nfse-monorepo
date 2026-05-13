<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_log', function (Blueprint $table) {
            $table->id();

            $table->foreignId('cliente_id')->nullable()->constrained('cliente');

            $table->string('acao', 60);                // ex: nfse.emitir, nfse.cancelar, nfse.consultar
            $table->string('recurso_tipo', 50)->nullable(); // ex: NfseEmissao, Cliente
            $table->string('recurso_id', 50)->nullable();   // id ou chave_acesso

            $table->string('ip_origem', 45)->nullable();
            $table->string('user_agent', 500)->nullable();

            $table->text('dados_request')->nullable();   // json string
            $table->text('dados_response')->nullable();  // json string

            // append-only: só data_criacao
            $table->timestamp('data_criacao')->useCurrent();

            $table->index(['cliente_id', 'data_criacao']);
            $table->index(['acao', 'data_criacao']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_log');
    }
};
