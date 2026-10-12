<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $agora = now();
        $sistemaId = DB::table('sistemas_veiculo')->where('nome', 'Freios')->value('id');

        if (! $sistemaId) {
            return;
        }

        DB::table('tipos_manutencao')
            ->where('nome', 'Troca de pastilhas ou lonas')
            ->update([
                'nome' => 'Troca de pastilhas',
                'sistema_veiculo_id' => $sistemaId,
                'updated_at' => $agora,
            ]);

        DB::table('tipos_manutencao')->updateOrInsert(
            ['nome' => 'Troca dos discos de freio'],
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

    public function down(): void
    {
        DB::table('tipos_manutencao')->where('nome', 'Troca dos discos de freio')->delete();
        DB::table('tipos_manutencao')
            ->where('nome', 'Troca de pastilhas')
            ->update(['nome' => 'Troca de pastilhas ou lonas', 'updated_at' => now()]);
    }
};
