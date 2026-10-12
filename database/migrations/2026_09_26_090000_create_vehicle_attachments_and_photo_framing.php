<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('veiculos', function (Blueprint $table) {
            $table->string('codigo_seguranca_crv', 30)->nullable()->after('renavam');
        });
        Schema::table('fotos_veiculos', function (Blueprint $table) {
            $table->unsignedTinyInteger('posicao_x')->default(50)->after('principal');
            $table->unsignedTinyInteger('posicao_y')->default(50)->after('posicao_x');
        });
        Schema::create('anexos_veiculos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('veiculo_id')->constrained('veiculos')->cascadeOnDelete();
            $table->string('tipo', 40);
            $table->string('nome_original');
            $table->string('arquivo');
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('tamanho')->nullable();
            $table->timestamps();
            $table->unique(['veiculo_id', 'tipo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anexos_veiculos');
        Schema::table('fotos_veiculos', fn (Blueprint $table) => $table->dropColumn(['posicao_x', 'posicao_y']));
        Schema::table('veiculos', fn (Blueprint $table) => $table->dropColumn('codigo_seguranca_crv'));
    }
};
