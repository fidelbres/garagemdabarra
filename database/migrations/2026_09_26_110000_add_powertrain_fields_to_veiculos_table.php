<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('veiculos', function (Blueprint $table) {
            $table->string('combustivel', 40)->nullable()->after('versao_motor');
            $table->string('cambio', 40)->nullable()->after('combustivel');
            $table->string('numero_motor', 80)->nullable()->after('cambio');
        });
    }

    public function down(): void
    {
        Schema::table('veiculos', function (Blueprint $table) {
            $table->dropColumn(['combustivel', 'cambio', 'numero_motor']);
        });
    }
};
