<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ponto extends Model
{
    protected $table = 'pontos';

    protected $fillable = [
        'colaborador_id',
        'datahora',
        'justificativa',
        'cancelado',
    ];

    protected $casts = [
        'datahora' => 'datetime',
        'cancelado' => 'boolean',
    ];

    public function colaborador()
    {
        return $this->belongsTo(Colaborador::class);
    }
}
