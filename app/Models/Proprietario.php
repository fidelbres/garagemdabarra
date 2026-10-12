<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Proprietario extends Model
{
    use SoftDeletes;

    protected $table = 'proprietarios';

    protected $fillable = ['user_id', 'nome', 'telefone', 'whatsapp', 'email', 'cpf', 'data_nascimento', 'endereco', 'cidade', 'estado', 'cep', 'preferencia_contato', 'observacoes', 'ativo'];

    protected function casts(): array
    {
        return ['data_nascimento' => 'date', 'ativo' => 'boolean'];
    }

    public function veiculos() { return $this->hasMany(Veiculo::class); }
    public function contratos() { return $this->hasMany(Contrato::class); }
    public function visitas() { return $this->hasMany(Visita::class); }

    protected function cpf(): Attribute { return Attribute::make(get:fn($v)=>$v?preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/','$1.$2.$3-$4',$v):null,set:fn($v)=>$v?preg_replace('/\D/','',$v):null); }
    protected function cep(): Attribute { return Attribute::make(get:fn($v)=>$v&&strlen($v)===8?substr($v,0,5).'-'.substr($v,5):$v,set:fn($v)=>$v?preg_replace('/\D/','',$v):null); }
    protected function telefone(): Attribute { return Attribute::make(get:fn($v)=>$this->formatarTelefone($v),set:fn($v)=>$v?preg_replace('/\D/','',$v):null); }
    protected function whatsapp(): Attribute { return Attribute::make(get:fn($v)=>$this->formatarTelefone($v),set:fn($v)=>$v?preg_replace('/\D/','',$v):null); }
    private function formatarTelefone(?string $v): ?string { if(!$v)return null;return strlen($v)===11?preg_replace('/(\d{2})(\d{5})(\d{4})/','($1) $2-$3',$v):preg_replace('/(\d{2})(\d{4})(\d{4})/','($1) $2-$3',$v); }
}
