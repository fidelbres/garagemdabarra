<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PrestadorServico extends Model
{
    use SoftDeletes;

    protected $table = 'prestadores_servico';
    protected $fillable = ['nome', 'local', 'endereco', 'telefone', 'whatsapp', 'pessoa_contato', 'atuacao', 'ativo'];
    protected function casts(): array { return ['ativo' => 'boolean']; }
    public function manutencoes() { return $this->hasMany(Manutencao::class); }
    public function pecasCompradas() { return $this->hasMany(PecaComprada::class); }
    public function itensFornecidos() { return $this->hasMany(ItemManutencao::class, 'fornecedor_id'); }
}
