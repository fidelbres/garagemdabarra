<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Contrato extends Model { use SoftDeletes; protected $table = 'contratos'; protected $guarded = []; public function proprietario(){return $this->belongsTo(Proprietario::class);} public function plano(){return $this->belongsTo(Plano::class);} }
