<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class InspecaoEntradaResposta extends Model { protected $table='inspecao_entrada_respostas';protected $guarded=[];public function inspecao(){return $this->belongsTo(InspecaoEntrada::class,'inspecao_entrada_id');} }
