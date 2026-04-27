<?php

use Livewire\Component;
use App\Models\Ponto;
use App\Models\Colaborador;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public $colaborador_id, $datahora, $ponto_id, $justificativa;
    public $isModalOpen = false;
    public $isDeleteModalOpen = false;
    public $pontoIdToDelete = null;
    public $search = '';

    public function mount()
    {
        $this->datahora = now()->format('Y-m-d\TH:i');
    }

    public function with()
    {
        return [
            'pontos' => Ponto::with('colaborador')
                ->join('colaboradores', 'pontos.colaborador_id', '=', 'colaboradores.id')
                ->select('pontos.*')
                ->where('colaboradores.nome', 'like', '%' . $this->search . '%')
                ->orderBy('colaboradores.nome', 'asc')
                ->orderBy('pontos.datahora', 'desc')
                ->paginate(10),
            'colaboradores' => Colaborador::where('ativo', true)->orderBy('nome')->get(),
        ];
    }

    public function create()
    {
        $this->resetInputFields();
        $this->datahora = now()->format('Y-m-d\TH:i');
        $this->isModalOpen = true;
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
        $this->isDeleteModalOpen = false;
        $this->justificativa = '';
    }

    private function resetInputFields()
    {
        $this->colaborador_id = '';
        $this->datahora = '';
        $this->ponto_id = '';
        $this->justificativa = '';
    }

    public function store()
    {
        $rules = [
            'colaborador_id' => 'required|exists:colaboradores,id',
            'datahora' => 'required|date',
        ];

        // Se estiver editando, justificativa é obrigatória
        if ($this->ponto_id) {
            $rules['justificativa'] = 'required|min:5';
        }

        $messages = [
            'colaborador_id.required' => 'Selecione um colaborador.',
            'colaborador_id.exists' => 'Colaborador selecionado é inválido.',
            'datahora.required' => 'A data e hora são obrigatórias.',
            'datahora.date' => 'Informe uma data e hora válidas.',
            'justificativa.required' => 'A justificativa é obrigatória para alterações.',
            'justificativa.min' => 'A justificativa deve ter pelo menos 5 caracteres.',
        ];

        $this->validate($rules, $messages);

        Ponto::updateOrCreate(['id' => $this->ponto_id], [
            'colaborador_id' => $this->colaborador_id,
            'datahora' => $this->datahora,
            'justificativa' => $this->justificativa,
        ]);

        session()->flash('message', $this->ponto_id ? 'Registro de ponto atualizado com justificativa.' : 'Ponto registrado com sucesso.');

        $this->closeModal();
        $this->resetInputFields();
    }

    public function edit($id)
    {
        $ponto = Ponto::findOrFail($id);

        if ($ponto->cancelado) {
            session()->flash('error', 'Registros cancelados não podem ser editados.');
            return;
        }

        $this->ponto_id = $id;
        $this->colaborador_id = $ponto->colaborador_id;
        $this->datahora = $ponto->datahora->format('Y-m-d\TH:i');
        $this->justificativa = $ponto->justificativa;
        $this->isModalOpen = true;
    }

    public function confirmDelete($id)
    {
        $ponto = Ponto::findOrFail($id);
        if ($ponto->cancelado)
            return;

        $this->pontoIdToDelete = $id;
        $this->justificativa = '';
        $this->isDeleteModalOpen = true;
    }

    public function delete()
    {
        $this->validate([
            'justificativa' => 'required|min:5'
        ]);

        $ponto = Ponto::find($this->pontoIdToDelete);
        $ponto->update([
            'cancelado' => true,
            'justificativa' => $this->justificativa
        ]);

        session()->flash('message', 'Registro de ponto cancelado com sucesso.');
        $this->closeModal();
    }
};
?>

<div>
    <div class="search-bar">
        <input type="text" class="form-control" placeholder="Pesquisar por colaborador..." wire:model.live="search">
        <button wire:click="create()" class="btn btn-primary no-wrap">
            <i class="fas fa-clock"></i> Registrar Ponto
        </button>
    </div>

    @if (session()->has('message'))
        <div class="alert-success">
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert-danger"
            style="background: #fee2e2; color: #991b1b; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem;">
            {{ session('error') }}
        </div>
    @endif

    <div class="card">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Colaborador</th>
                        <th>Data</th>
                        <th>Hora</th>
                        <th>Justificativa</th>
                        <th style="width: 150px;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pontos as $ponto)
                        <tr class="{{ $ponto->cancelado ? 'cancelled-row' : '' }}">
                            <td class="font-semibold">{{ $ponto->colaborador->nome }}</td>
                            <td>{{ $ponto->datahora->format('d/m/Y') }}</td>
                            <td>{{ $ponto->datahora->format('H:i') }}</td>
                            <td>
                                @if($ponto->cancelado)
                                    <span class="badge badge-danger" style="margin-right: 0.5rem;">Cancelado</span>
                                @endif
                                <span class="text-sm">{{ $ponto->justificativa ?: '-' }}</span>
                            </td>
                            <td class="action-btns">
                                @if(!$ponto->cancelado)
                                    <button wire:click="edit({{ $ponto->id }})" class="btn btn-secondary btn-sm"
                                        title="Editar Ponto">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button wire:click="confirmDelete({{ $ponto->id }})" class="btn btn-danger btn-sm"
                                        title="Cancelar Ponto">
                                        <i class="fas fa-ban"></i>
                                    </button>
                                @else
                                    <span class="text-xs">Registro Inativo</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="empty-state">
                                Nenhum registro de ponto encontrado.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="pagination-wrapper">
            {{ $pontos->links('vendor.pagination.custom') }}
        </div>
    </div>

    @if($isModalOpen)
        <div class="modal-backdrop">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title">{{ $ponto_id ? 'Editar Ponto' : 'Registrar Ponto' }}</h2>
                    <button wire:click="closeModal()" class="btn-icon">&times;</button>
                </div>
                <form wire:submit.prevent="store()">
                    <div class="form-group">
                        <label for="colaborador_id">Colaborador</label>
                        <select class="form-control" id="colaborador_id" wire:model="colaborador_id">
                            <option value="">Selecione um colaborador</option>
                            @foreach($colaboradores as $colab)
                                <option value="{{ $colab->id }}">{{ $colab->nome }} ({{ $colab->formatted_cpf }})</option>
                            @endforeach
                        </select>
                        @error('colaborador_id') <span class="text-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="datahora">Data e Hora</label>
                        <input type="datetime-local" class="form-control" id="datahora" wire:model="datahora">
                        @error('datahora') <span class="text-error">{{ $message }}</span> @enderror
                    </div>

                    @if($ponto_id)
                        <div class="form-group">
                            <label for="justificativa">Justificativa da Alteração (Obrigatório)</label>
                            <textarea class="form-control" id="justificativa" wire:model="justificativa"
                                placeholder="Descreva o motivo da alteração..."></textarea>
                            @error('justificativa') <span class="text-error">{{ $message }}</span> @enderror
                        </div>
                    @endif

                    <div class="modal-footer">
                        <button type="button" wire:click="closeModal()" class="btn btn-secondary">Cancelar</button>
                        <button type="submit" class="btn btn-primary">{{ $ponto_id ? 'Atualizar' : 'Registrar' }}</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if($isDeleteModalOpen)
        <div class="modal-backdrop">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title">Cancelar Registro de Ponto</h2>
                    <button wire:click="closeModal()" class="btn-icon">&times;</button>
                </div>
                <div class="mb-4">
                    <p>Para cancelar este registro, você deve fornecer uma justificativa obrigatória.</p>
                    <div class="form-group mt-4">
                        <label for="just_cancel">Justificativa do Cancelamento</label>
                        <textarea class="form-control" id="just_cancel" wire:model="justificativa"
                            placeholder="Motivo do cancelamento..."></textarea>
                        @error('justificativa') <span class="text-error">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" wire:click="closeModal()" class="btn btn-secondary">Voltar</button>
                    <button type="button" wire:click="delete()" class="btn btn-danger">Confirmar Cancelamento</button>
                </div>
            </div>
        </div>
    @endif
</div>