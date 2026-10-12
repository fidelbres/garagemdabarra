<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class FotoVeiculo extends Model
{
    protected $table = 'fotos_veiculos';
    protected $fillable = ['veiculo_id', 'galeria_veiculo_id', 'manutencao_id', 'arquivo', 'legenda', 'categoria', 'data_foto', 'principal', 'posicao_x', 'posicao_y', 'ordem'];
    protected function casts(): array { return ['data_foto' => 'datetime', 'principal' => 'boolean']; }
    public function veiculo() { return $this->belongsTo(Veiculo::class); }
    public function manutencao() { return $this->belongsTo(Manutencao::class); }
    public function galeria() { return $this->belongsTo(GaleriaVeiculo::class, 'galeria_veiculo_id'); }

    public function getInlineUrlAttribute(): string
    {
        if (! Storage::disk('public')->exists($this->arquivo)) {
            return '';
        }

        return Storage::disk('public')->url($this->arquivo);
    }
}
