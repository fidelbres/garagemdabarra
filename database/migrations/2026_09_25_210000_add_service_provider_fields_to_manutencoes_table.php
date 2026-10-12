<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('manutencoes', function (Blueprint $table) {
            $table->string('empresa_servico')->nullable()->after('quilometragem');
            $table->string('local_servico')->nullable()->after('empresa_servico');
            $table->string('telefone_servico', 30)->nullable()->after('local_servico');
            $table->string('whatsapp_servico', 30)->nullable()->after('telefone_servico');
        });
    }

    public function down(): void
    {
        Schema::table('manutencoes', fn (Blueprint $table) => $table->dropColumn(['empresa_servico', 'local_servico', 'telefone_servico', 'whatsapp_servico']));
    }
};
