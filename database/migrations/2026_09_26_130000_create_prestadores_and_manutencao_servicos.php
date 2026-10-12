<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Remove somente estruturas incompletas deixadas por uma tentativa interrompida desta migração.
        Schema::dropIfExists('manutencao_tipo_manutencao');
        if (Schema::hasColumn('manutencoes', 'prestador_servico_id')) {
            Schema::table('manutencoes', fn (Blueprint $table) => $table->dropConstrainedForeignId('prestador_servico_id'));
        }
        Schema::dropIfExists('prestadores_servico');

        Schema::create('prestadores_servico', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('local')->nullable();
            $table->string('endereco')->nullable();
            $table->string('telefone', 30)->nullable();
            $table->string('whatsapp', 30)->nullable();
            $table->string('pessoa_contato')->nullable();
            $table->boolean('ativo')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('manutencoes', function (Blueprint $table) {
            $table->foreignId('prestador_servico_id')->nullable()->after('quilometragem')->constrained('prestadores_servico')->nullOnDelete();
        });

        Schema::create('manutencao_tipo_manutencao', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manutencao_id')->constrained('manutencoes')->cascadeOnDelete();
            $table->foreignId('tipo_manutencao_id')->constrained('tipos_manutencao')->restrictOnDelete();
            $table->date('proxima_data_prevista')->nullable();
            $table->unsignedBigInteger('proxima_quilometragem_prevista')->nullable();
            $table->timestamps();
            $table->unique(['manutencao_id', 'tipo_manutencao_id'], 'manutencao_tipo_unico');
        });

        DB::statement('INSERT INTO manutencao_tipo_manutencao (manutencao_id, tipo_manutencao_id, proxima_data_prevista, proxima_quilometragem_prevista, created_at, updated_at) SELECT id, tipo_manutencao_id, proxima_data_prevista, proxima_quilometragem_prevista, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP FROM manutencoes WHERE tipo_manutencao_id IS NOT NULL');
    }

    public function down(): void
    {
        Schema::dropIfExists('manutencao_tipo_manutencao');
        Schema::table('manutencoes', fn (Blueprint $table) => $table->dropConstrainedForeignId('prestador_servico_id'));
        Schema::dropIfExists('prestadores_servico');
    }
};
