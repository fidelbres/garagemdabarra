<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class OficinaSeeder extends Seeder
{
    public function run(): void
    {
        $agora = now();

        DB::table('users')->updateOrInsert(
            ['email' => 'admin@garagemdabarra.com.br'],
            ['name' => 'Administrador', 'password' => Hash::make('Garagem@2026'), 'tipo' => 'administrador', 'ativo' => true, 'created_at' => $agora, 'updated_at' => $agora],
        );

        $sistemas = [
            ['Geral e diagnóstico', '⌕'], ['Motor', '⚙'], ['Arrefecimento', '◉'], ['Alimentação e combustível', '◈'],
            ['Ignição', 'ϟ'], ['Parte elétrica', '⌁'], ['Freios', '⊙'], ['Embreagem', '◌'], ['Transmissão e câmbio', '⇄'],
            ['Suspensão', '⌇'], ['Direção', '◇'], ['Ar-condicionado', '❄'], ['Escapamento', '≈'],
            ['Rodas e pneus', '◎'], ['Carroceria e funilaria', '▱'], ['Vidros', '▰'], ['Limpadores e lavadores', '≋'], ['Tapeçaria e interior', '▤'], ['Estética e limpeza', '✦'],
        ];
        foreach ($sistemas as $ordem => [$nome, $icone]) {
            DB::table('sistemas_veiculo')->updateOrInsert(['nome' => $nome], ['icone' => $icone, 'ordem' => $ordem + 1, 'ativo' => true, 'created_at' => $agora, 'updated_at' => $agora]);
        }
        $sistemaIds = DB::table('sistemas_veiculo')->pluck('id', 'nome');

        $tipos = [
            ['Geral e diagnóstico', 'Revisão geral', 12, 10000], ['Geral e diagnóstico', 'Diagnóstico técnico', null, null],
            ['Motor', 'Troca de óleo do motor', 6, 5000], ['Motor', 'Troca do filtro de óleo', 6, 5000], ['Motor', 'Troca do filtro de ar do motor', 12, 10000], ['Motor', 'Troca de correia dentada', 48, 50000], ['Motor', 'Regulagem do motor', 12, 10000],
            ['Arrefecimento', 'Revisão do sistema de arrefecimento', 12, 10000], ['Arrefecimento', 'Troca do líquido de arrefecimento', 24, 30000], ['Arrefecimento', 'Reparo de radiador', null, null],
            ['Alimentação e combustível', 'Revisão do carburador', 12, 10000], ['Alimentação e combustível', 'Regulagem do carburador', 6, 5000], ['Alimentação e combustível', 'Limpeza do tanque e linhas', null, null], ['Alimentação e combustível', 'Limpeza do TBI', null, null], ['Alimentação e combustível', 'Troca do filtro de combustível', null, null], ['Alimentação e combustível', 'Troca das linhas de combustível', null, null], ['Alimentação e combustível', 'Troca da bomba de combustível', null, null],
            ['Ignição', 'Revisão de ignição', 12, 10000], ['Ignição', 'Troca de velas e cabos', 12, 10000], ['Ignição', 'Regulagem do ponto', null, null],
            ['Parte elétrica', 'Revisão da parte elétrica', 12, null], ['Parte elétrica', 'Troca da bateria', 36, null], ['Parte elétrica', 'Reparo de alternador', null, null], ['Parte elétrica', 'Reparo de motor de partida', null, null],
            ['Freios', 'Revisão de freios', 12, 10000], ['Freios', 'Troca de pastilhas', null, null], ['Freios', 'Troca dos discos de freio', null, null], ['Freios', 'Troca do cilindro mestre de freio', null, null], ['Freios', 'Troca do fluido de freio', 24, 20000],
            ['Embreagem', 'Revisão da embreagem', null, null], ['Embreagem', 'Troca do kit de embreagem', null, null], ['Embreagem', 'Regulagem da embreagem', null, null],
            ['Transmissão e câmbio', 'Troca de óleo da caixa/câmbio', 24, 40000], ['Transmissão e câmbio', 'Reparo da caixa de marcha', null, null], ['Transmissão e câmbio', 'Revisão do diferencial', 24, 40000],
            ['Suspensão', 'Revisão de suspensão', 12, 10000], ['Suspensão', 'Troca de amortecedores', null, null], ['Suspensão', 'Troca de buchas e pivôs', null, null],
            ['Direção', 'Revisão de direção', 12, 10000], ['Direção', 'Alinhamento de direção', 6, 10000], ['Direção', 'Reparo da caixa de direção', null, null],
            ['Ar-condicionado', 'Revisão do ar-condicionado', 12, null], ['Ar-condicionado', 'Troca do filtro da cabine', 12, 10000], ['Ar-condicionado', 'Carga de gás', null, null], ['Ar-condicionado', 'Reparo do compressor', null, null],
            ['Escapamento', 'Revisão do escapamento', 12, 10000], ['Escapamento', 'Troca de silencioso', null, null],
            ['Rodas e pneus', 'Rodízio de pneus', 6, 10000], ['Rodas e pneus', 'Balanceamento', 6, 10000], ['Rodas e pneus', 'Reparo ou troca de pneus', null, null],
            ['Carroceria e funilaria', 'Funilaria e reparo de carroceria', null, null], ['Carroceria e funilaria', 'Pintura e retoque', null, null], ['Carroceria e funilaria', 'Tratamento de ferrugem', null, null],
            ['Vidros', 'Reparo ou troca de vidros', null, null], ['Vidros', 'Reparo de máquina de vidro', null, null], ['Vidros', 'Troca de borrachas e canaletas', null, null],
            ['Limpadores e lavadores', 'Troca das palhetas do limpador', null, null], ['Limpadores e lavadores', 'Troca da bomba do lavador do para-brisa', null, null],
            ['Tapeçaria e interior', 'Reparo de bancos e tapeçaria', null, null], ['Tapeçaria e interior', 'Reparo do forro de teto', null, null], ['Tapeçaria e interior', 'Restauração do painel e acabamentos', null, null],
            ['Estética e limpeza', 'Lavagem completa do carro', null, null], ['Estética e limpeza', 'Polimento', null, null], ['Estética e limpeza', 'Troca de insulfilm', null, null],
        ];

        foreach ($tipos as [$sistema, $nome, $meses, $quilometros]) {
            DB::table('tipos_manutencao')->updateOrInsert(
                ['nome' => $nome],
                [
                    'sistema_veiculo_id' => $sistemaIds[$sistema],
                    'tipo_periodicidade' => $meses && $quilometros ? 'tempo_ou_quilometragem' : ($meses ? 'tempo' : 'nenhuma'),
                    'intervalo_meses' => $meses,
                    'intervalo_quilometros' => $quilometros,
                    'ativo' => true,
                    'created_at' => $agora,
                    'updated_at' => $agora,
                ],
            );
        }

        $planos = [
            ['Básico', 1, 'mensal', false, ['Acompanhamento periódico', 'Histórico de manutenções', 'Alertas preventivos']],
            ['Intermediário', 1, 'mensal', true, ['Acompanhamento periódico', 'Alertas preventivos', 'Apoio em feiras e encontros']],
            ['Garagem', 2, 'mensal', true, ['Atendimento prioritário', 'Controle preventivo', 'Apoio em feiras e eventos']],
        ];

        foreach ($planos as [$nome, $visitas, $periodicidade, $eventos, $servicos]) {
            DB::table('planos')->updateOrInsert(
                ['nome' => $nome],
                [
                    'descricao' => "Plano {$nome} da Garagem da Barra",
                    'valor_mensal' => 0,
                    'quantidade_visitas' => $visitas,
                    'periodicidade_visitas' => $periodicidade,
                    'apoio_eventos' => $eventos,
                    'servicos_incluidos' => json_encode($servicos, JSON_UNESCAPED_UNICODE),
                    'ativo' => true,
                    'created_at' => $agora,
                    'updated_at' => $agora,
                ],
            );
        }
    }
}
