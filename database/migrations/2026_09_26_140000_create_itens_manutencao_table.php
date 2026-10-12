<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('itens_manutencao', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manutencao_id')->constrained('manutencoes')->cascadeOnDelete();
            $table->foreignId('tipo_manutencao_id')->nullable()->constrained('tipos_manutencao')->nullOnDelete();
            $table->enum('categoria', ['peca', 'servico']);
            $table->string('descricao');
            $table->decimal('quantidade', 10, 3)->default(1);
            $table->decimal('valor_unitario', 12, 2)->default(0);
            $table->decimal('valor_total', 12, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('itens_manutencao');
    }
};
