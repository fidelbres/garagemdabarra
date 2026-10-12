<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnexoManutencao extends Model
{
    protected $table = 'anexos_manutencoes';
    protected $fillable = ['manutencao_id', 'nome_original', 'arquivo', 'mime_type', 'tamanho'];

    public function manutencao()
    {
        return $this->belongsTo(Manutencao::class);
    }
}
