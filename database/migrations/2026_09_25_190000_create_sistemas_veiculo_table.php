<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sistemas_veiculo', function (Blueprint $table) {
            $table->id();
            $table->string('nome')->unique();
            $table->string('icone', 12)->nullable();
            $table->text('descricao')->nullable();
            $table->unsignedSmallInteger('ordem')->default(0);
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });

        Schema::table('tipos_manutencao', function (Blueprint $table) {
            $table->foreignId('sistema_veiculo_id')->nullable()->after('id')->constrained('sistemas_veiculo')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tipos_manutencao', fn (Blueprint $table) => $table->dropConstrainedForeignId('sistema_veiculo_id'));
        Schema::dropIfExists('sistemas_veiculo');
    }
};
