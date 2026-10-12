<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Plano extends Model { use SoftDeletes; protected $table = 'planos'; protected $guarded = []; protected function casts(): array { return ['servicos_incluidos'=>'array','apoio_eventos'=>'boolean','ativo'=>'boolean']; } }
