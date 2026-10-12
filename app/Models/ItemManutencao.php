<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemManutencao extends Model
{
    protected $table = 'itens_manutencao';
    protected $fillable = ['manutencao_id', 'tipo_manutencao_id', 'fornecedor_id', 'categoria', 'descricao', 'quantidade', 'valor_unitario', 'valor_total'];
    protected function casts(): array { return ['quantidade' => 'decimal:3', 'valor_unitario' => 'decimal:2', 'valor_total' => 'decimal:2']; }
    public function manutencao() { return $this->belongsTo(Manutencao::class); }
    public function tipo() { return $this->belongsTo(TipoManutencao::class, 'tipo_manutencao_id'); }
    public function fornecedor() { return $this->belongsTo(PrestadorServico::class, 'fornecedor_id'); }
}
