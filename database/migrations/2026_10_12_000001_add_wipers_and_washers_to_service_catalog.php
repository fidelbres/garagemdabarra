<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $agora = now();

        DB::table('sistemas_veiculo')->updateOrInsert(
            ['nome' => 'Limpadores e lavadores'],
            [
                'icone' => '≋',
                'descricao' => 'Palhetas, mecanismos e sistema de lavagem dos vidros.',
                'ordem' => ((int) DB::table('sistemas_veiculo')->max('ordem')) + 1,
                'ativo' => true,
                'created_at' => $agora,
                'updated_at' => $agora,
            ],
        );

        $sistemaId = DB::table('sistemas_veiculo')
            ->where('nome', 'Limpadores e lavadores')
            ->value('id');

        foreach (['Troca das palhetas do limpador', 'Troca da bomba do lavador do para-brisa'] as $nome) {
            DB::table('tipos_manutencao')->updateOrInsert(
                ['nome' => $nome],
                [
                    'sistema_veiculo_id' => $sistemaId,
                    'tipo_periodicidade' => 'nenhuma',
                    'intervalo_meses' => null,
                    'intervalo_quilometros' => null,
                    'antecedencia_alerta_dias' => 30,
                    'antecedencia_alerta_quilometros' => 1000,
                    'ativo' => true,
                    'created_at' => $agora,
                    'updated_at' => $agora,
                    'deleted_at' => null,
                ],
            );
        }
    }

    public function down(): void
    {
        DB::table('tipos_manutencao')
            ->whereIn('nome', ['Troca das palhetas do limpador', 'Troca da bomba do lavador do para-brisa'])
            ->delete();

        DB::table('sistemas_veiculo')
            ->where('nome', 'Limpadores e lavadores')
            ->delete();
    }
};
