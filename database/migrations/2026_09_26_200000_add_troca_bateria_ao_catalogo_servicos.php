<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $agora = now();
        $sistema = DB::table('sistemas_veiculo')->whereIn('nome', ['Parte elétrica', 'Elétrica'])->first();

        if (! $sistema) {
            return;
        }

        if ($sistema->nome !== 'Parte elétrica') {
            DB::table('sistemas_veiculo')->where('id', $sistema->id)->update([
                'nome' => 'Parte elétrica',
                'updated_at' => $agora,
            ]);
        }

        DB::table('tipos_manutencao')->updateOrInsert(
            ['nome' => 'Troca da bateria'],
            [
                'sistema_veiculo_id' => $sistema->id,
                'tipo_periodicidade' => 'tempo',
                'intervalo_meses' => 36,
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
        DB::table('tipos_manutencao')->where('nome', 'Troca da bateria')->delete();
        DB::table('sistemas_veiculo')->where('nome', 'Parte elétrica')->update([
            'nome' => 'Elétrica',
            'updated_at' => now(),
        ]);
    }
};
