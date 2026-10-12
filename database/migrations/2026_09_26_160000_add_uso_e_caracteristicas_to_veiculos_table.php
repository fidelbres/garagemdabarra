<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('veiculos', function (Blueprint $table) {
            $table->string('tipo_uso', 30)->default('colecao')->after('nome');
            $table->boolean('possui_4x4')->default(false)->after('cambio');
            $table->boolean('possui_ar_condicionado')->default(false)->after('possui_4x4');
            $table->index('tipo_uso');
        });
    }

    public function down(): void
    {
        Schema::table('veiculos', function (Blueprint $table) {
            $table->dropIndex(['tipo_uso']);
            $table->dropColumn(['tipo_uso', 'possui_4x4', 'possui_ar_condicionado']);
        });
    }
};
