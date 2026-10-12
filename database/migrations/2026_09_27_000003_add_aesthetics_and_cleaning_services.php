<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $agora = now();
        DB::table('sistemas_veiculo')->updateOrInsert(
            ['nome' => 'Estética e limpeza'],
            ['icone' => '✦', 'descricao' => null, 'ordem' => 18, 'ativo' => true, 'created_at' => $agora, 'updated_at' => $agora],
        );
        $sistemaId = DB::table('sistemas_veiculo')->where('nome', 'Estética e limpeza')->value('id');
        foreach (['Lavagem completa do carro', 'Polimento', 'Troca de insulfilm'] as $nome) {
            DB::table('tipos_manutencao')->updateOrInsert(
                ['nome' => $nome],
                ['sistema_veiculo_id' => $sistemaId, 'tipo_periodicidade' => 'nenhuma', 'antecedencia_alerta_dias' => 30, 'antecedencia_alerta_quilometros' => 1000, 'ativo' => true, 'created_at' => $agora, 'updated_at' => $agora],
            );
        }
    }

    public function down(): void
    {
        $sistemaId = DB::table('sistemas_veiculo')->where('nome', 'Estética e limpeza')->value('id');
        DB::table('tipos_manutencao')->where('sistema_veiculo_id', $sistemaId)->whereIn('nome', ['Lavagem completa do carro', 'Polimento', 'Troca de insulfilm'])->delete();
        DB::table('sistemas_veiculo')->where('id', $sistemaId)->delete();
    }
};
