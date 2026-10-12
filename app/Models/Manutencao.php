<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Manutencao extends Model
{
    use SoftDeletes;

    protected $table = 'manutencoes';
    protected $fillable = ['veiculo_id', 'tipo_manutencao_id', 'prestador_servico_id', 'registrado_por', 'natureza', 'data_manutencao', 'quilometragem', 'valor', 'empresa_servico', 'local_servico', 'telefone_servico', 'whatsapp_servico', 'descricao_problema', 'servico_executado', 'observacoes', 'proxima_data_prevista', 'proxima_quilometragem_prevista'];
    protected function casts(): array { return ['data_manutencao' => 'date', 'proxima_data_prevista' => 'date', 'valor' => 'decimal:2']; }
    public function veiculo() { return $this->belongsTo(Veiculo::class); }
    public function tipo() { return $this->belongsTo(TipoManutencao::class, 'tipo_manutencao_id'); }
    public function tipos() { return $this->belongsToMany(TipoManutencao::class, 'manutencao_tipo_manutencao')->withPivot(['proxima_data_prevista', 'proxima_quilometragem_prevista'])->withTimestamps(); }
    public function prestador() { return $this->belongsTo(PrestadorServico::class, 'prestador_servico_id'); }
    public function fotos() { return $this->hasMany(FotoVeiculo::class); }
    public function anexos() { return $this->hasMany(AnexoManutencao::class); }
    public function itens() { return $this->hasMany(ItemManutencao::class); }
    public function inspecaoEntrada() { return $this->hasOne(InspecaoEntrada::class); }
    public function getCustoTotalAttribute(): float { return (float) ($this->valor ?? 0) + (float) $this->itens->sum('valor_total'); }
}
