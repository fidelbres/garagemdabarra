<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnexoVeiculo extends Model
{
    protected $table = 'anexos_veiculos';
    protected $fillable = ['veiculo_id', 'tipo', 'nome_original', 'arquivo', 'mime_type', 'tamanho'];
    public function veiculo() { return $this->belongsTo(Veiculo::class); }
}
