<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GaleriaVeiculo extends Model
{
    protected $table = 'galerias_veiculos';
    protected $fillable = ['veiculo_id', 'nome', 'data_registro', 'descricao'];
    protected function casts(): array { return ['data_registro' => 'date']; }
    public function veiculo() { return $this->belongsTo(Veiculo::class); }
    public function fotos() { return $this->hasMany(FotoVeiculo::class); }
}
