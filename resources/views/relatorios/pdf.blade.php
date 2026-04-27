<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Relatório de Ponto</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .header { text-align: center; margin-bottom: 20px; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 10px; }
        .summary { margin-top: 20px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Relatório de Ponto - Ação Direta</h1>
        <p>Período: {{ \Carbon\Carbon::parse($data_inicio)->format('d/m/Y') }} até {{ \Carbon\Carbon::parse($data_fim)->format('d/m/Y') }}</p>
        @if($colaborador)
            <p>Colaborador: {{ $colaborador->nome }} ({{ $colaborador->formatted_cpf }})</p>
        @else
            <p>Colaborador: Todos</p>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>Data</th>
                <th>Hora</th>
                <th>Justificativa</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pontos->groupBy('colaborador_id') as $colab_id => $grupo)
            <tr>
                <td colspan="3" style="background-color: #eee; font-weight: bold;">
                    Colaborador: {{ $grupo->first()->colaborador->nome }}
                </td>
            </tr>
            @foreach($grupo as $ponto)
            <tr>
                <td>{{ $ponto->datahora->format('d/m/Y') }}</td>
                <td>{{ $ponto->datahora->format('H:i') }}</td>
                <td>{{ $ponto->justificativa ?: '-' }}</td>
            </tr>
            @endforeach
            @endforeach
        </tbody>
    </table>

    <div class="summary">
        Total de registros: {{ $pontos->count() }}
    </div>

    <div class="footer">
        Gerado em {{ now()->format('d/m/Y H:i:s') }}
    </div>
</body>
</html>
