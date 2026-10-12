<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class ChecklistPadraoSeeder extends Seeder {
 public function run():void {
  $grupos=[
   'Carroceria e pintura'=>['Para-choque dianteiro','Capô','Para-lama dianteiro esquerdo','Para-lama dianteiro direito','Porta dianteira esquerda','Porta dianteira direita','Porta traseira esquerda','Porta traseira direita','Painel lateral esquerdo','Painel lateral direito','Tampa traseira / porta-malas','Para-choque traseiro','Teto','Pintura geral'],
   'Vidros e iluminação'=>['Para-brisa','Vidro traseiro','Vidros laterais','Retrovisores','Faróis','Lanternas traseiras','Setas e luzes de freio','Limpadores e esguicho'],
   'Rodas e segurança'=>['Pneu dianteiro esquerdo','Pneu dianteiro direito','Pneu traseiro esquerdo','Pneu traseiro direito','Estepe','Rodas / calotas','Triângulo','Macaco e chave de roda','Cintos de segurança'],
   'Interior e acessórios'=>['Bancos e revestimentos','Painel e instrumentos','Tapetes','Som / multimídia','Ar-condicionado / ventilação','Chave e controle','Documentos deixados no veículo'],
   'Funcionamento e vazamentos'=>['Partida do motor','Faróis, lanternas e buzina','Freio de estacionamento','Vazamento aparente de óleo','Vazamento aparente de água / combustível','Ruídos aparentes'],
  ];$o=0;$agora=now();foreach($grupos as $categoria=>$itens)foreach($itens as $nome)DB::table('checklist_itens_padrao')->updateOrInsert(['categoria'=>$categoria,'nome'=>$nome],['ordem'=>++$o,'ativo'=>true,'created_at'=>$agora,'updated_at'=>$agora]);
 }
}
