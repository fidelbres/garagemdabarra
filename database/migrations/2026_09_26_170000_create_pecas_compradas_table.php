<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prestadores_servico', function (Blueprint $table) {
            $table->string('atuacao', 20)->default('ambos')->after('pessoa_contato')->index();
        });

        Schema::create('pecas_compradas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('veiculo_id')->constrained('veiculos')->cascadeOnDelete();
            $table->foreignId('prestador_servico_id')->nullable()->constrained('prestadores_servico')->nullOnDelete();
            $table->string('descricao');
            $table->string('marca')->nullable();
            $table->string('codigo')->nullable();
            $table->decimal('quantidade', 10, 2)->default(1);
            $table->decimal('valor_total', 12, 2)->nullable();
            $table->date('data_compra');
            $table->text('observacoes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['veiculo_id', 'data_compra']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pecas_compradas');
        Schema::table('prestadores_servico', fn (Blueprint $table) => $table->dropColumn('atuacao'));
    }
};
