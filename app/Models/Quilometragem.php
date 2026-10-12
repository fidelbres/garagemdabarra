<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quilometragem extends Model
{
    protected $table = 'quilometragens';
    protected $fillable = ['veiculo_id', 'manutencao_id', 'registrado_por', 'quilometragem', 'registrada_em', 'origem', 'observacoes'];
    protected function casts(): array { return ['registrada_em' => 'datetime']; }
    public function veiculo() { return $this->belongsTo(Veiculo::class); }
}
