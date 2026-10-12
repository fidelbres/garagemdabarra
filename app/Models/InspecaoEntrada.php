<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class InspecaoEntrada extends Model { protected $table='inspecoes_entrada';protected $guarded=[];protected function casts():array{return ['realizada_em'=>'datetime','veiculo_limpo'=>'boolean','confirmada'=>'boolean'];}public function respostas(){return $this->hasMany(InspecaoEntradaResposta::class);}public function manutencao(){return $this->belongsTo(Manutencao::class);} }
