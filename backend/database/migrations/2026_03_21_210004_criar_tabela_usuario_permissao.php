<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuario_permissao', function (Blueprint $table) {
            $table->foreignId('usuario_id')->constrained('usuario')->cascadeOnDelete();
            $table->foreignId('permissao_id')->constrained('permissao')->cascadeOnDelete();
            $table->enum('tipo', ['permitir', 'negar'])->default('permitir');
            $table->timestamp('data_cadastro')->useCurrent();

            $table->unique(['usuario_id', 'permissao_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuario_permissao');
    }
};
