<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PecaComprada extends Model
{
    use SoftDeletes;

    protected $table = 'pecas_compradas';
    protected $fillable = ['veiculo_id', 'prestador_servico_id', 'descricao', 'marca', 'codigo', 'quantidade', 'valor_total', 'data_compra', 'observacoes'];
    protected function casts(): array { return ['data_compra' => 'date', 'quantidade' => 'decimal:2', 'valor_total' => 'decimal:2']; }
    public function veiculo() { return $this->belongsTo(Veiculo::class); }
    public function fornecedor() { return $this->belongsTo(PrestadorServico::class, 'prestador_servico_id'); }
}
