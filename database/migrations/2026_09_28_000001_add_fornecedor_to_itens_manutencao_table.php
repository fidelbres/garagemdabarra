<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('itens_manutencao', function (Blueprint $table) {
            $table->foreignId('fornecedor_id')->nullable()->after('tipo_manutencao_id')->constrained('prestadores_servico')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('itens_manutencao', fn (Blueprint $table) => $table->dropConstrainedForeignId('fornecedor_id'));
    }
};
