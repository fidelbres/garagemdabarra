<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up():void {
  Schema::create('checklist_itens_padrao',function(Blueprint $t){$t->id();$t->string('categoria',80);$t->string('nome');$t->unsignedSmallInteger('ordem')->default(0);$t->boolean('ativo')->default(true);$t->timestamps();$t->unique(['categoria','nome']);});
  Schema::create('inspecoes_entrada',function(Blueprint $t){$t->id();$t->foreignId('manutencao_id')->unique()->constrained('manutencoes')->cascadeOnDelete();$t->foreignId('veiculo_id')->constrained('veiculos')->restrictOnDelete();$t->foreignId('realizado_por')->nullable()->constrained('users')->nullOnDelete();$t->dateTime('realizada_em');$t->enum('nivel_combustivel',['reserva','1/4','1/2','3/4','cheio','nao_verificado'])->default('nao_verificado');$t->boolean('veiculo_limpo')->nullable();$t->text('observacoes_gerais')->nullable();$t->boolean('confirmada')->default(true);$t->timestamps();});
  Schema::create('inspecao_entrada_respostas',function(Blueprint $t){$t->id();$t->foreignId('inspecao_entrada_id')->constrained('inspecoes_entrada')->cascadeOnDelete();$t->foreignId('checklist_item_padrao_id')->nullable()->constrained('checklist_itens_padrao')->nullOnDelete();$t->string('categoria',80);$t->string('item');$t->enum('situacao',['ok','avariado','ausente','nao_verificado']);$t->string('observacao')->nullable();$t->timestamps();$t->index(['inspecao_entrada_id','situacao']);});
 }
 public function down():void {Schema::dropIfExists('inspecao_entrada_respostas');Schema::dropIfExists('inspecoes_entrada');Schema::dropIfExists('checklist_itens_padrao');}
};
