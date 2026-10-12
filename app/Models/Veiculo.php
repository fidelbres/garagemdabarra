<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Veiculo extends Model
{
    use SoftDeletes;

    protected $table = 'veiculos';

    protected $fillable = ['proprietario_id', 'nome', 'tipo_uso', 'marca_veiculo_id', 'marca', 'modelo_veiculo_id', 'modelo', 'nome_modelo_completo', 'versao_motor', 'combustivel', 'cambio', 'possui_4x4', 'possui_ar_condicionado', 'possui_manual', 'numero_motor', 'cor', 'ano', 'ano_modelo', 'placa', 'chassi', 'renavam', 'codigo_seguranca_crv', 'quilometragem_atual', 'data_compra', 'local_compra', 'local_anterior', 'local_atual', 'cidade_atual', 'estado_atual', 'observacoes', 'ativo'];

    protected function casts(): array
    {
        return ['ativo' => 'boolean', 'possui_4x4' => 'boolean', 'possui_ar_condicionado' => 'boolean', 'possui_manual' => 'boolean', 'data_compra' => 'date'];
    }

    public function proprietario()
    {
        return $this->belongsTo(Proprietario::class);
    }

    public function manutencoes()
    {
        return $this->hasMany(Manutencao::class);
    }

    public function pecasCompradas()
    {
        return $this->hasMany(PecaComprada::class);
    }

    public function especificacoes()
    {
        return $this->hasMany(EspecificacaoVeiculo::class);
    }

    public function quilometragens()
    {
        return $this->hasMany(Quilometragem::class);
    }

    public function fotos()
    {
        return $this->hasMany(FotoVeiculo::class);
    }

    public function anexos()
    {
        return $this->hasMany(AnexoVeiculo::class);
    }

    public function galerias()
    {
        return $this->hasMany(GaleriaVeiculo::class);
    }

    public function fotoPrincipal()
    {
        return $this->hasOne(FotoVeiculo::class)
            ->whereNull('galeria_veiculo_id')
            ->whereNull('manutencao_id')
            ->ofMany(['principal' => 'max', 'ordem' => 'min', 'id' => 'min']);
    }

    public function marcaCatalogo()
    {
        return $this->belongsTo(MarcaVeiculo::class, 'marca_veiculo_id');
    }

    public function modeloCatalogo()
    {
        return $this->belongsTo(ModeloVeiculo::class, 'modelo_veiculo_id');
    }

    protected function placa(): Attribute
    {
        return Attribute::make(get: fn ($v) => $v && strlen($v) === 7 ? substr($v, 0, 3).'-'.substr($v, 3) : $v, set: fn ($v) => $v ? mb_strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $v)) : null);
    }
}
