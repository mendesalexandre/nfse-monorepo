<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cliente', function (Blueprint $table) {
            $table->id();

            // Identificação
            $table->string('nome_empresa');
            $table->string('cnpj', 14)->unique();
            $table->string('email');
            $table->string('telefone', 20)->nullable();

            // Credenciais API (multi-tenant)
            $table->string('api_key_hash', 255)->unique()->index();
            $table->string('client_id', 32)->unique();
            $table->string('client_secret_hash', 255);

            // Certificado A1 — criptografado em repouso
            $table->text('pfx_encrypted');           // Crypt::encryptString do binário base64
            $table->text('pfx_senha_encrypted');     // Crypt::encryptString da senha
            $table->date('cert_validade');
            $table->string('cert_cnpj', 14);

            // Dados do prestador (NFS-e)
            $table->string('inscricao_municipal');
            $table->string('razao_social_prestador');
            $table->string('codigo_municipio_ibge', 7);
            $table->string('uf', 2);
            $table->string('cep', 8);
            $table->string('logradouro');
            $table->string('numero', 20);
            $table->string('bairro');
            $table->string('complemento')->nullable();

            // Tributação
            $table->unsignedTinyInteger('regime_especial_tributacao')->default(0); // 0..7
            $table->unsignedTinyInteger('simples_nacional')->default(1);           // 1..3

            // Toggles
            $table->string('ambiente', 20)->default('homologacao'); // producao | homologacao
            $table->boolean('incluir_ibscbs')->default(false);
            $table->boolean('is_ativo')->default(true);

            // Timestamps pt_BR
            $table->timestamp('data_criacao')->useCurrent();
            $table->timestamp('data_alteracao')->useCurrent()->useCurrentOnUpdate();
            $table->softDeletes('data_exclusao');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cliente');
    }
};
