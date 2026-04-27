<?php

use Livewire\Component;
use App\Models\Ponto;
use App\Models\Colaborador;

new class extends Component
{
    public $colaborador_id, $data_inicio, $data_fim;
    public $gerado = false;

    public function mount()
    {
        $this->data_inicio = now()->startOfMonth()->format('Y-m-d');
        $this->data_fim = now()->format('Y-m-d');
    }

    public function pesquisar()
    {
        $this->gerado = true;
    }

    public function with()
    {
        $colaboradores = Colaborador::orderBy('nome')->get();
        
        if (!$this->gerado) {
            return [
                'colaboradores' => $colaboradores,
                'pontos_agrupados' => collect(),
            ];
        }

        $query = Ponto::with('colaborador')->where('cancelado', false);

        if ($this->colaborador_id) {
            $query->where('colaborador_id', $this->colaborador_id);
        }

        if ($this->data_inicio) {
            $query->whereDate('datahora', '>=', $this->data_inicio);
        }

        if ($this->data_fim) {
            $query->whereDate('datahora', '<=', $this->data_fim);
        }

        $pontos = $query->orderBy('colaborador_id')
                        ->orderBy('datahora', 'asc')
                        ->get();

        return [
            'colaboradores' => $colaboradores,
            'pontos_agrupados' => $pontos->groupBy('colaborador_id'),
        ];
    }
};
?>

<div>
    <div class="card mb-4 p-8">
        <h3 class="font-semibold mb-4">Filtros do Relatório</h3>
        <div class="flex-center gap-4">
            <div class="form-group w-full">
                <label for="colab_filter">Colaborador</label>
                <select class="form-control" id="colab_filter" wire:model="colaborador_id">
                    <option value="">Todos os Colaboradores</option>
                    @foreach($colaboradores as $colab)
                        <option value="{{ $colab->id }}">{{ $colab->nome }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group w-full">
                <label for="date_start">Data Início</label>
                <input type="date" class="form-control" id="date_start" wire:model="data_inicio">
            </div>
            <div class="form-group w-full">
                <label for="date_end">Data Fim</label>
                <input type="date" class="form-control" id="date_end" wire:model="data_fim">
            </div>
            <div class="form-group">
                <label>&nbsp;</label>
                <button wire:click="pesquisar" class="btn btn-primary no-wrap w-full">
                    <i class="fas fa-filter"></i> Filtrar
                </button>
            </div>
        </div>
    </div>

    @if($gerado)
    <div class="card">
        <div class="flex-between mb-4 p-8" style="padding-bottom: 0;">
            <h3 class="font-semibold">Lançamentos Encontrados</h3>
            @if($pontos_agrupados->isNotEmpty())
                <a href="{{ route('relatorios.exportar', ['colaborador_id' => $colaborador_id, 'data_inicio' => $data_inicio, 'data_fim' => $data_fim]) }}" target="_blank" class="btn btn-success" style="padding: 0.5rem 1.5rem; font-size: 0.875rem;">
                    <i class="fas fa-file-pdf"></i> &nbsp; Exportar PDF
                </a>
            @endif
        </div>
        
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th style="width: 150px;">Data</th>
                        <th style="width: 100px;">Hora</th>
                        <th>Justificativa/Obs.</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pontos_agrupados as $colab_id => $pontos)
                    <tr style="background: #f8fafc;">
                        <td colspan="3" style="font-weight: 700; color: var(--primary-color); border-bottom: 2px solid var(--primary-color);">
                            <i class="fas fa-user-tie"></i> {{ $pontos->first()->colaborador->nome }}
                        </td>
                    </tr>
                    @foreach($pontos as $ponto)
                    <tr>
                        <td>{{ $ponto->datahora->format('d/m/Y') }}</td>
                        <td class="font-bold">{{ $ponto->datahora->format('H:i') }}</td>
                        <td class="text-sm text-muted">{{ $ponto->justificativa ?: '-' }}</td>
                    </tr>
                    @endforeach
                    @empty
                    <tr>
                        <td colspan="3" class="empty-state">
                            Nenhum registro encontrado para os filtros selecionados.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        

    </div>
    @else
    <div class="empty-state card">
        <i class="fas fa-info-circle" style="font-size: 2rem; margin-bottom: 1rem; color: var(--primary-color);"></i>
        <p>Preencha os filtros acima e clique em <strong>Filtrar</strong> para gerar o relatório agrupado.</p>
    </div>
    @endif
</div>