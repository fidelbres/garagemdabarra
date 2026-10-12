<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $agora = now();
        $servicos = [
            ['sistema' => 'Motor', 'nome' => 'Troca do filtro de ar do motor', 'meses' => 12, 'quilometros' => 10000],
            ['sistema' => 'Ar-condicionado', 'nome' => 'Troca do filtro da cabine', 'meses' => 12, 'quilometros' => 10000],
        ];

        foreach ($servicos as $servico) {
            $sistemaId = DB::table('sistemas_veiculo')->where('nome', $servico['sistema'])->value('id');
            if (! $sistemaId) continue;

            DB::table('tipos_manutencao')->updateOrInsert(
                ['nome' => $servico['nome']],
                [
                    'sistema_veiculo_id' => $sistemaId,
                    'tipo_periodicidade' => 'tempo_ou_quilometragem',
                    'intervalo_meses' => $servico['meses'],
                    'intervalo_quilometros' => $servico['quilometros'],
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
        DB::table('tipos_manutencao')->whereIn('nome', ['Troca do filtro de ar do motor', 'Troca do filtro da cabine'])->delete();
    }
};
