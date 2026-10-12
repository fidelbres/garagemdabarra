<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TipoManutencao extends Model
{
    use SoftDeletes;

    protected $table = 'tipos_manutencao';
    protected $fillable = ['sistema_veiculo_id', 'nome', 'descricao', 'tipo_periodicidade', 'intervalo_meses', 'intervalo_quilometros', 'antecedencia_alerta_dias', 'antecedencia_alerta_quilometros', 'ativo'];
    protected function casts(): array { return ['ativo' => 'boolean']; }
    public function manutencoes() { return $this->hasMany(Manutencao::class); }
    public function registros() { return $this->belongsToMany(Manutencao::class, 'manutencao_tipo_manutencao')->withPivot(['proxima_data_prevista', 'proxima_quilometragem_prevista'])->withTimestamps(); }
    public function sistema() { return $this->belongsTo(SistemaVeiculo::class, 'sistema_veiculo_id'); }
}
