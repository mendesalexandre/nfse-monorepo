<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nfse_emissao', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('cliente');

            // Identificação SEFIN
            $table->string('chave_acesso', 50)->nullable()->index();
            $table->string('numero_nfse', 20)->nullable();
            $table->unsignedSmallInteger('c_stat')->nullable();
            $table->string('x_motivo', 500)->nullable();

            // DPS (numeração local)
            $table->unsignedInteger('numero_dps');
            $table->string('serie_dps', 5)->default('1');

            // Tomador (LGPD — criptografado)
            $table->text('tomador_documento_encrypted');
            $table->text('tomador_nome_encrypted');

            // Valores (claros — métricas / relatórios)
            $table->decimal('valor_servicos', 15, 2);
            $table->decimal('valor_iss', 15, 2)->default(0);

            // Discriminação criptografada (LGPD)
            $table->text('discriminacao_encrypted');

            // Audit do request original e response do SEFIN
            $table->text('request_payload')->nullable();   // jsonb (encrypted via cast)
            $table->text('response_xml_encrypted')->nullable();

            // Datas
            $table->dateTime('data_emissao');
            $table->dateTime('data_processamento')->nullable();

            // Status interno (estado da nossa view)
            $table->string('status', 20)->default('pendente'); // pendente|emitida|rejeitada|cancelada|substituida|erro

            // Timestamps pt_BR
            $table->timestamp('data_criacao')->useCurrent();
            $table->timestamp('data_alteracao')->useCurrent()->useCurrentOnUpdate();

            $table->index(['cliente_id', 'data_criacao']);
            $table->unique(['cliente_id', 'serie_dps', 'numero_dps']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nfse_emissao');
    }
};
