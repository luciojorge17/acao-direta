<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Colaborador extends Model
{
    protected $table = 'colaboradores';

    protected $fillable = [
        'nome',
        'ativo',
        'data_nascimento',
        'cpf',
    ];

    protected $casts = [
        'ativo' => 'boolean',
        'data_nascimento' => 'date',
    ];

    public function pontos()
    {
        return $this->hasMany(Ponto::class);
    }

    protected function formattedCpf(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: function () {
                $cpf = preg_replace('/\D/', '', $this->cpf);
                if (strlen($cpf) != 11) return $this->cpf;
                return substr($cpf, 0, 3) . '.' . substr($cpf, 3, 3) . '.' . substr($cpf, 6, 3) . '-' . substr($cpf, 9, 2);
            }
        );
    }
}
