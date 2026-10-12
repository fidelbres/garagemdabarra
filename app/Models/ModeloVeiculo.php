<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ModeloVeiculo extends Model { protected $table='modelos_veiculos'; protected $fillable=['marca_veiculo_id','nome','ano_inicio','ano_fim','ativo']; public function marca(){return $this->belongsTo(MarcaVeiculo::class,'marca_veiculo_id');} }
