<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SistemaVeiculo extends Model
{
    protected $table = 'sistemas_veiculo';
    protected $fillable = ['nome', 'icone', 'descricao', 'ordem', 'ativo'];
    protected function casts(): array { return ['ativo' => 'boolean']; }
    public function servicos() { return $this->hasMany(TipoManutencao::class, 'sistema_veiculo_id'); }
}
