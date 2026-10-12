<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EspecificacaoVeiculo extends Model
{
    use SoftDeletes;

    protected $table = 'especificacoes_veiculos';
    protected $fillable = ['veiculo_id', 'tipo_manutencao_id', 'categoria', 'item', 'especificacao_recomendada', 'marca_preferida', 'referencia', 'referencias_equivalentes', 'marca_instalada', 'modelo_instalado', 'referencia_instalada', 'instalado_em', 'quilometragem_instalacao', 'observacoes'];
    protected function casts(): array { return ['instalado_em' => 'date']; }
    public function veiculo() { return $this->belongsTo(Veiculo::class); }
    public function tipoServico() { return $this->belongsTo(TipoManutencao::class, 'tipo_manutencao_id'); }
}
