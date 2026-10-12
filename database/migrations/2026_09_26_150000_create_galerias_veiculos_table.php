<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('galerias_veiculos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('veiculo_id')->constrained('veiculos')->cascadeOnDelete();
            $table->string('nome');
            $table->date('data_registro')->nullable();
            $table->text('descricao')->nullable();
            $table->timestamps();
        });
        Schema::table('fotos_veiculos', function (Blueprint $table) {
            $table->foreignId('galeria_veiculo_id')->nullable()->after('veiculo_id')->constrained('galerias_veiculos')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('fotos_veiculos', fn (Blueprint $table) => $table->dropConstrainedForeignId('galeria_veiculo_id'));
        Schema::dropIfExists('galerias_veiculos');
    }
};
