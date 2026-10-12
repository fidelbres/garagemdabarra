<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('especificacoes_veiculos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('veiculo_id')->constrained('veiculos')->cascadeOnDelete();
            $table->foreignId('tipo_manutencao_id')->nullable()->constrained('tipos_manutencao')->nullOnDelete();
            $table->string('categoria', 40)->index();
            $table->string('item');
            $table->text('especificacao_recomendada')->nullable();
            $table->string('marca_preferida')->nullable();
            $table->string('referencia')->nullable();
            $table->text('referencias_equivalentes')->nullable();
            $table->string('marca_instalada')->nullable();
            $table->string('modelo_instalado')->nullable();
            $table->string('referencia_instalada')->nullable();
            $table->date('instalado_em')->nullable();
            $table->unsignedBigInteger('quilometragem_instalacao')->nullable();
            $table->text('observacoes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['veiculo_id', 'categoria']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('especificacoes_veiculos');
    }
};
