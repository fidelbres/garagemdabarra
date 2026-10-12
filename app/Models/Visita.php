<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Visita extends Model { use SoftDeletes; protected $table = 'visitas'; protected $guarded = []; protected function casts(): array{return ['inicio_previsto'=>'datetime','fim_previsto'=>'datetime','realizada_em'=>'datetime'];} public function proprietario(){return $this->belongsTo(Proprietario::class);} }
