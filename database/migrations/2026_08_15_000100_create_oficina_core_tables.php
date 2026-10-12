<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('tipo', ['administrador', 'oficina', 'cliente'])->default('cliente')->after('password');
            $table->boolean('ativo')->default(true)->after('tipo');
        });

        Schema::create('proprietarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->unique()->constrained('users')->nullOnDelete();
            $table->string('nome');
            $table->string('telefone', 30)->nullable();
            $table->string('whatsapp', 30)->nullable();
            $table->string('email')->nullable()->index();
            $table->string('cpf', 14)->nullable()->unique();
            $table->date('data_nascimento')->nullable();
            $table->string('endereco')->nullable();
            $table->string('cidade', 100)->nullable();
            $table->char('estado', 2)->nullable();
            $table->string('cep', 10)->nullable();
            $table->enum('preferencia_contato', ['whatsapp', 'email', 'telefone', 'aplicativo'])->nullable();
            $table->text('observacoes')->nullable();
            $table->boolean('ativo')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('veiculos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proprietario_id')->constrained('proprietarios')->restrictOnDelete();
            $table->string('nome');
            $table->string('marca', 100);
            $table->string('modelo', 100);
            $table->unsignedSmallInteger('ano')->nullable();
            $table->unsignedSmallInteger('ano_modelo')->nullable();
            $table->string('placa', 10)->nullable()->unique();
            $table->string('chassi', 50)->nullable()->unique();
            $table->unsignedBigInteger('quilometragem_atual')->default(0);
            $table->text('observacoes')->nullable();
            $table->boolean('ativo')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['proprietario_id', 'ativo']);
        });

        Schema::create('tipos_manutencao', function (Blueprint $table) {
            $table->id();
            $table->string('nome')->unique();
            $table->text('descricao')->nullable();
            $table->enum('tipo_periodicidade', ['nenhuma', 'tempo', 'quilometragem', 'tempo_ou_quilometragem'])->default('nenhuma');
            $table->unsignedSmallInteger('intervalo_meses')->nullable();
            $table->unsignedInteger('intervalo_quilometros')->nullable();
            $table->unsignedSmallInteger('antecedencia_alerta_dias')->default(30);
            $table->unsignedInteger('antecedencia_alerta_quilometros')->default(1000);
            $table->boolean('ativo')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('manutencoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('veiculo_id')->constrained('veiculos')->restrictOnDelete();
            $table->foreignId('tipo_manutencao_id')->nullable()->constrained('tipos_manutencao')->nullOnDelete();
            $table->foreignId('registrado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('natureza', ['periodica', 'avulsa'])->default('avulsa')->index();
            $table->date('data_manutencao')->index();
            $table->unsignedBigInteger('quilometragem');
            $table->text('descricao_problema')->nullable();
            $table->text('servico_executado');
            $table->text('observacoes')->nullable();
            $table->date('proxima_data_prevista')->nullable()->index();
            $table->unsignedBigInteger('proxima_quilometragem_prevista')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['veiculo_id', 'data_manutencao']);
        });

        Schema::create('fotos_veiculos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('veiculo_id')->constrained('veiculos')->cascadeOnDelete();
            $table->foreignId('manutencao_id')->nullable()->constrained('manutencoes')->nullOnDelete();
            $table->string('arquivo');
            $table->string('legenda')->nullable();
            $table->enum('categoria', ['geral', 'motor', 'interior', 'carroceria', 'componente', 'antes', 'depois', 'outra'])->default('geral');
            $table->dateTime('data_foto')->nullable();
            $table->boolean('principal')->default(false);
            $table->unsignedInteger('ordem')->default(0);
            $table->timestamps();
            $table->index(['veiculo_id', 'categoria']);
        });

        Schema::create('quilometragens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('veiculo_id')->constrained('veiculos')->cascadeOnDelete();
            $table->foreignId('manutencao_id')->nullable()->constrained('manutencoes')->nullOnDelete();
            $table->foreignId('registrado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedBigInteger('quilometragem');
            $table->dateTime('registrada_em')->index();
            $table->enum('origem', ['manutencao', 'visita', 'manual', 'cliente'])->default('manual');
            $table->text('observacoes')->nullable();
            $table->timestamps();
            $table->index(['veiculo_id', 'registrada_em']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quilometragens');
        Schema::dropIfExists('fotos_veiculos');
        Schema::dropIfExists('manutencoes');
        Schema::dropIfExists('tipos_manutencao');
        Schema::dropIfExists('veiculos');
        Schema::dropIfExists('proprietarios');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['tipo', 'ativo']);
        });
    }
};
