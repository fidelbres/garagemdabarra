<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('veiculos', function (Blueprint $table) {
            $table->string('nome_modelo_completo')->nullable()->after('modelo');
            $table->string('versao_motor', 120)->nullable()->after('nome_modelo_completo');
            $table->string('cor', 80)->nullable()->after('versao_motor');
            $table->date('data_compra')->nullable()->after('quilometragem_atual');
            $table->string('local_compra')->nullable()->after('data_compra');
            $table->string('local_anterior')->nullable()->after('local_compra');
            $table->string('local_atual')->nullable()->after('local_anterior');
            $table->string('cidade_atual', 120)->nullable()->after('local_atual');
            $table->char('estado_atual', 2)->nullable()->after('cidade_atual');
        });
    }

    public function down(): void
    {
        Schema::table('veiculos', function (Blueprint $table) {
            $table->dropColumn(['nome_modelo_completo', 'versao_motor', 'cor', 'data_compra', 'local_compra', 'local_anterior', 'local_atual', 'cidade_atual', 'estado_atual']);
        });
    }
};
