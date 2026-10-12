<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class MarcaVeiculo extends Model { protected $table='marcas_veiculos'; protected $fillable=['nome','ativo']; public function modelos(){return $this->hasMany(ModeloVeiculo::class);} }
