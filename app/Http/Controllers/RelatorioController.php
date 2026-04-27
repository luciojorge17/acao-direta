<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ponto;
use App\Models\Colaborador;
use Barryvdh\DomPDF\Facade\Pdf;

class RelatorioController extends Controller
{
    public function export(Request $request)
    {
        $data_inicio = $request->query('data_inicio');
        $data_fim = $request->query('data_fim');
        $colaborador_id = $request->query('colaborador_id');

        $query = Ponto::with('colaborador')->where('cancelado', false);

        if ($colaborador_id) {
            $query->where('colaborador_id', $colaborador_id);
        }

        if ($data_inicio) {
            $query->whereDate('datahora', '>=', $data_inicio);
        }

        if ($data_fim) {
            $query->whereDate('datahora', '<=', $data_fim);
        }

        $pontos = $query->join('colaboradores', 'pontos.colaborador_id', '=', 'colaboradores.id')
                        ->select('pontos.*')
                        ->orderBy('colaboradores.nome', 'asc')
                        ->orderBy('pontos.datahora', 'asc')
                        ->get();
        $colaborador = $colaborador_id ? Colaborador::find($colaborador_id) : null;

        $pdf = Pdf::loadView('relatorios.pdf', [
            'pontos' => $pontos,
            'colaborador' => $colaborador,
            'data_inicio' => $data_inicio,
            'data_fim' => $data_fim
        ]);

        return $pdf->download('relatorio-ponto-' . now()->format('Y-m-d') . '.pdf');
    }
}
