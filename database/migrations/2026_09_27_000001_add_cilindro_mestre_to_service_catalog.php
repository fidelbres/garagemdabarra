<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $sistemaId = DB::table('sistemas_veiculo')->where('nome', 'Freios')->value('id');
        if (! $sistemaId) return;

        DB::table('tipos_manutencao')->updateOrInsert(
            ['nome' => 'Troca do cilindro mestre de freio'],
            ['sistema_veiculo_id' => $sistemaId, 'tipo_periodicidade' => 'nenhuma', 'antecedencia_alerta_dias' => 30, 'antecedencia_alerta_quilometros' => 1000, 'ativo' => true, 'updated_at' => now(), 'created_at' => now()],
        );
    }

    public function down(): void
    {
        DB::table('tipos_manutencao')->where('nome', 'Troca do cilindro mestre de freio')->delete();
    }
};
