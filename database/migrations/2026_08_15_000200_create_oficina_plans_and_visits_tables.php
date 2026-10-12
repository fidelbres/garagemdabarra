<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('planos', function (Blueprint $table) {
            $table->id();
            $table->string('nome')->unique();
            $table->text('descricao')->nullable();
            $table->decimal('valor_mensal', 10, 2);
            $table->unsignedSmallInteger('quantidade_visitas')->default(1);
            $table->enum('periodicidade_visitas', ['semanal', 'quinzenal', 'mensal', 'personalizada'])->default('mensal');
            $table->boolean('apoio_eventos')->default(false);
            $table->json('servicos_incluidos')->nullable();
            $table->text('observacoes')->nullable();
            $table->boolean('ativo')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('contratos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proprietario_id')->constrained('proprietarios')->restrictOnDelete();
            $table->foreignId('plano_id')->constrained('planos')->restrictOnDelete();
            $table->date('data_inicio');
            $table->date('data_fim')->nullable();
            $table->decimal('valor_mensal', 10, 2);
            $table->enum('situacao', ['ativo', 'suspenso', 'cancelado', 'encerrado'])->default('ativo')->index();
            $table->string('regra_visita')->nullable();
            $table->enum('frequencia_atendimento', ['semanal', 'quinzenal', 'mensal', 'personalizada'])->default('mensal');
            $table->date('proxima_visita')->nullable()->index();
            $table->text('observacoes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['proprietario_id', 'situacao']);
        });

        Schema::create('visitas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proprietario_id')->constrained('proprietarios')->restrictOnDelete();
            $table->foreignId('contrato_id')->nullable()->constrained('contratos')->nullOnDelete();
            $table->dateTime('inicio_previsto')->index();
            $table->dateTime('fim_previsto')->nullable();
            $table->dateTime('realizada_em')->nullable();
            $table->enum('situacao', ['programada', 'aguardando_confirmacao', 'confirmada', 'realizada', 'cancelada'])->default('programada')->index();
            $table->string('token_confirmacao', 64)->nullable()->unique();
            $table->dateTime('confirmada_em')->nullable();
            $table->text('observacoes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('veiculos_visita', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visita_id')->constrained('visitas')->cascadeOnDelete();
            $table->foreignId('veiculo_id')->constrained('veiculos')->restrictOnDelete();
            $table->text('solicitacao_cliente')->nullable();
            $table->text('observacoes_oficina')->nullable();
            $table->boolean('prioritario')->default(true);
            $table->timestamps();
            $table->unique(['visita_id', 'veiculo_id']);
        });

        Schema::create('agenda', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proprietario_id')->nullable()->constrained('proprietarios')->nullOnDelete();
            $table->foreignId('veiculo_id')->nullable()->constrained('veiculos')->nullOnDelete();
            $table->foreignId('visita_id')->nullable()->constrained('visitas')->cascadeOnDelete();
            $table->enum('tipo', ['visita', 'servico', 'manutencao', 'evento', 'compromisso']);
            $table->string('titulo');
            $table->text('descricao')->nullable();
            $table->dateTime('inicio')->index();
            $table->dateTime('fim')->nullable();
            $table->enum('situacao', ['agendado', 'confirmado', 'concluido', 'cancelado'])->default('agendado')->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agenda');
        Schema::dropIfExists('veiculos_visita');
        Schema::dropIfExists('visitas');
        Schema::dropIfExists('contratos');
        Schema::dropIfExists('planos');
    }
};
