<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $sistemaId = DB::table('sistemas_veiculo')->where('nome', 'Alimentação e combustível')->value('id');
        if (! $sistemaId) return;

        foreach (['Troca das linhas de combustível', 'Troca da bomba de combustível'] as $nome) {
            DB::table('tipos_manutencao')->updateOrInsert(
                ['nome' => $nome],
                ['sistema_veiculo_id' => $sistemaId, 'tipo_periodicidade' => 'nenhuma', 'antecedencia_alerta_dias' => 30, 'antecedencia_alerta_quilometros' => 1000, 'ativo' => true, 'created_at' => now(), 'updated_at' => now()],
            );
        }
    }

    public function down(): void
    {
        DB::table('tipos_manutencao')->whereIn('nome', ['Troca das linhas de combustível', 'Troca da bomba de combustível'])->delete();
    }
};
