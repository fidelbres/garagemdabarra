<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alertas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proprietario_id')->constrained('proprietarios')->cascadeOnDelete();
            $table->foreignId('veiculo_id')->nullable()->constrained('veiculos')->cascadeOnDelete();
            $table->foreignId('manutencao_id')->nullable()->constrained('manutencoes')->nullOnDelete();
            $table->foreignId('visita_id')->nullable()->constrained('visitas')->nullOnDelete();
            $table->enum('tipo', ['manutencao_proxima', 'manutencao_vencida', 'lembrete_visita', 'relacionamento']);
            $table->enum('situacao', ['pendente', 'processando', 'enviado', 'lido', 'descartado', 'erro'])->default('pendente')->index();
            $table->string('titulo');
            $table->text('mensagem');
            $table->dateTime('disparar_em')->index();
            $table->dateTime('resolvido_em')->nullable();
            $table->timestamps();
            $table->index(['proprietario_id', 'tipo', 'situacao']);
        });

        Schema::create('modelos_mensagem', function (Blueprint $table) {
            $table->id();
            $table->string('nome')->unique();
            $table->enum('canal', ['whatsapp', 'email', 'aplicativo']);
            $table->string('assunto')->nullable();
            $table->text('conteudo');
            $table->json('variaveis')->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('notificacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proprietario_id')->constrained('proprietarios')->cascadeOnDelete();
            $table->foreignId('alerta_id')->nullable()->constrained('alertas')->nullOnDelete();
            $table->foreignId('modelo_mensagem_id')->nullable()->constrained('modelos_mensagem')->nullOnDelete();
            $table->enum('canal', ['whatsapp', 'email', 'aplicativo']);
            $table->string('destinatario');
            $table->string('assunto')->nullable();
            $table->text('conteudo');
            $table->enum('situacao', ['pendente', 'enviando', 'enviada', 'entregue', 'lida', 'falha'])->default('pendente')->index();
            $table->string('id_externo')->nullable()->index();
            $table->text('erro')->nullable();
            $table->dateTime('agendada_para')->nullable()->index();
            $table->dateTime('enviada_em')->nullable();
            $table->dateTime('lida_em')->nullable();
            $table->json('metadados')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notificacoes');
        Schema::dropIfExists('modelos_mensagem');
        Schema::dropIfExists('alertas');
    }
};
