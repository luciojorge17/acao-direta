<?php

use Livewire\Component;
use App\Models\Colaborador;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public $nome, $ativo = true, $data_nascimento, $cpf, $colaborador_id;
    public $isModalOpen = false;
    public $isDeleteModalOpen = false;
    public $colaboradorIdToDelete = null;
    public $search = '';

    public function with()
    {
        return [
            'colaboradores' => Colaborador::where('nome', 'like', '%' . $this->search . '%')
                ->orWhere('cpf', 'like', '%' . $this->search . '%')
                ->orderBy('nome')
                ->paginate(10),
        ];
    }

    public function create()
    {
        $this->resetInputFields();
        $this->openModal();
    }

    public function openModal()
    {
        $this->isModalOpen = true;
        $this->dispatch('modal-opened');
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
        $this->isDeleteModalOpen = false;
        $this->colaboradorIdToDelete = null;
    }

    private function resetInputFields()
    {
        $this->nome = '';
        $this->ativo = true;
        $this->data_nascimento = '';
        $this->cpf = '';
        $this->colaborador_id = '';
    }

    public function store()
    {
        $this->cpf = preg_replace('/\D/', '', $this->cpf);

        $rules = [
            'nome' => 'required|min:3',
            'data_nascimento' => 'required|date',
            'cpf' => 'required|min:11|unique:colaboradores,cpf' . ($this->colaborador_id ? ',' . $this->colaborador_id : ''),
            'ativo' => 'boolean',
        ];

        $messages = [
            'nome.required' => 'O nome é obrigatório.',
            'nome.min' => 'O nome deve ter pelo menos 3 caracteres.',
            'data_nascimento.required' => 'A data de nascimento é obrigatória.',
            'data_nascimento.date' => 'Informe uma data válida.',
            'cpf.required' => 'O CPF é obrigatório.',
            'cpf.min' => 'O CPF deve ter pelo menos 11 dígitos.',
            'cpf.unique' => 'Este CPF já está cadastrado para outro colaborador.',
        ];

        $this->validate($rules, $messages);

        Colaborador::updateOrCreate(['id' => $this->colaborador_id], [
            'nome' => $this->nome,
            'ativo' => $this->ativo,
            'data_nascimento' => $this->data_nascimento,
            'cpf' => $this->cpf,
        ]);

        session()->flash('message', $this->colaborador_id ? 'Colaborador atualizado com sucesso.' : 'Colaborador criado com sucesso.');

        $this->closeModal();
        $this->resetInputFields();
    }

    public function edit($id)
    {
        $colaborador = Colaborador::findOrFail($id);
        $this->colaborador_id = $id;
        $this->nome = $colaborador->nome;
        $this->ativo = $colaborador->ativo;
        $this->data_nascimento = $colaborador->data_nascimento->format('Y-m-d');

        $cpf = preg_replace('/\D/', '', $colaborador->cpf);
        $this->cpf = substr($cpf, 0, 3) . '.' . substr($cpf, 3, 3) . '.' . substr($cpf, 6, 3) . '-' . substr($cpf, 9, 2);

        $this->openModal();
    }

    public function confirmDelete($id)
    {
        $this->colaboradorIdToDelete = $id;
        $this->isDeleteModalOpen = true;
    }

    public function delete()
    {
        Colaborador::find($this->colaboradorIdToDelete)->delete();
        session()->flash('message', 'Colaborador excluído com sucesso.');
        $this->closeModal();
    }
};
?>

<div>
    <div class="search-bar">
        <input type="text" class="form-control" placeholder="Pesquisar por nome ou CPF..." wire:model.live="search">
        <button wire:click="create()" class="btn btn-primary no-wrap">
            <i class="fas fa-plus"></i> Novo Colaborador
        </button>
    </div>

    @if (session()->has('message'))
        <div class="alert-success">
            {{ session('message') }}
        </div>
    @endif

    <div class="card">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>CPF</th>
                        <th>Data Nasc.</th>
                        <th>Status</th>
                        <th style="width: 150px;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($colaboradores as $colab)
                        <tr>
                            <td>{{ $colab->nome }}</td>
                            <td>{{ $colab->formatted_cpf }}</td>
                            <td>{{ $colab->data_nascimento->format('d/m/Y') }}</td>
                            <td>
                                <span class="badge {{ $colab->ativo ? 'badge-success' : 'badge-danger' }}">
                                    {{ $colab->ativo ? 'Ativo' : 'Inativo' }}
                                </span>
                            </td>
                            <td class="action-btns">
                                <button wire:click="edit({{ $colab->id }})" class="btn btn-secondary btn-sm"
                                    title="Editar Colaborador">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button wire:click="confirmDelete({{ $colab->id }})" class="btn btn-danger btn-sm"
                                    title="Excluir Colaborador">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="empty-state">
                                Nenhum colaborador encontrado.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $colaboradores->links() }}
        </div>
    </div>

    @if($isModalOpen)
        <div class="modal-backdrop">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title">{{ $colaborador_id ? 'Editar Colaborador' : 'Novo Colaborador' }}</h2>
                    <button wire:click="closeModal()" class="btn-icon">&times;</button>
                </div>
                <form wire:submit.prevent="store()" autocomplete="off">
                    <div class="form-group">
                        <label for="nome">Nome Completo</label>
                        <input type="text" class="form-control" id="nome" wire:model="nome">
                        @error('nome') <span class="text-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="cpf">CPF</label>
                        <input type="text" class="form-control" id="cpf" wire:model="cpf" maxlength="14"
                            placeholder="000.000.000-00">
                        @error('cpf') <span class="text-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="data_nascimento">Data de Nascimento</label>
                        <input type="date" class="form-control" id="data_nascimento" wire:model="data_nascimento">
                        @error('data_nascimento') <span class="text-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group flex-center gap-3 mt-4">
                        <input type="checkbox" id="ativo" wire:model="ativo" class="form-checkbox">
                        <label for="ativo" class="font-semibold" style="margin: 0; cursor: pointer;">Ativo</label>
                    </div>
                    <div class="modal-footer">
                        <button type="button" wire:click="closeModal()" class="btn btn-secondary">Cancelar</button>
                        <button type="submit"
                            class="btn btn-primary">{{ $colaborador_id ? 'Atualizar' : 'Salvar' }}</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if($isDeleteModalOpen)
        <div class="modal-backdrop">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title">Confirmar Exclusão</h2>
                    <button wire:click="closeModal()" class="btn-icon">&times;</button>
                </div>
                <div class="mb-4">
                    Tem certeza que deseja excluir este colaborador? Esta ação não pode ser desfeita e pode afetar os
                    registros de ponto vinculados.
                </div>
                <div class="modal-footer">
                    <button type="button" wire:click="closeModal()" class="btn btn-secondary">Cancelar</button>
                    <button type="button" wire:click="delete()" class="btn btn-danger">Sim, Excluir</button>
                </div>
            </div>
        </div>
    @endif
</div>

@push('scripts')
    <script>
        $(document).on('input', '#cpf', function () {
            let value = $(this).val().replace(/\D/g, '');
            if (value.length > 11) value = value.slice(0, 11);

            let masked = value;
            if (value.length > 9) {
                masked = value.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
            } else if (value.length > 6) {
                masked = value.replace(/(\d{3})(\d{3})(\d{3})/, '$1.$2.$3');
            } else if (value.length > 3) {
                masked = value.replace(/(\d{3})(\d{3})/, '$1.$2');
            }

            $(this).val(masked);

            this.dispatchEvent(new Event('input'));
        });
    </script>
@endpush