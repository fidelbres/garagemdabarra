<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marcas_veiculos', function (Blueprint $table) {
            $table->id();
            $table->string('nome')->unique();
            $table->boolean('ativo')->default(true)->index();
            $table->timestamps();
        });
        Schema::create('modelos_veiculos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('marca_veiculo_id')->constrained('marcas_veiculos')->cascadeOnDelete();
            $table->string('nome');
            $table->unsignedSmallInteger('ano_inicio')->nullable();
            $table->unsignedSmallInteger('ano_fim')->nullable();
            $table->boolean('ativo')->default(true)->index();
            $table->timestamps();
            $table->unique(['marca_veiculo_id', 'nome']);
        });
        Schema::table('veiculos', function (Blueprint $table) {
            $table->foreignId('marca_veiculo_id')->nullable()->after('nome')->constrained('marcas_veiculos')->nullOnDelete();
            $table->foreignId('modelo_veiculo_id')->nullable()->after('marca')->constrained('modelos_veiculos')->nullOnDelete();
        });
    }
    public function down(): void
    {
        Schema::table('veiculos', function (Blueprint $table) {$table->dropConstrainedForeignId('modelo_veiculo_id');$table->dropConstrainedForeignId('marca_veiculo_id');});
        Schema::dropIfExists('modelos_veiculos');Schema::dropIfExists('marcas_veiculos');
    }
};
