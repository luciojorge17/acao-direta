<?php

use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public $name, $email, $password, $password_confirmation, $user_id;
    public $isModalOpen = false;
    public $isDeleteModalOpen = false;
    public $userIdToDelete = null;
    public $search = '';

    protected $rules = [
        'name' => 'required|min:3',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|min:6',
    ];

    public function with()
    {
        return [
            'users' => User::where('name', 'like', '%' . $this->search . '%')
                ->orWhere('email', 'like', '%' . $this->search . '%')
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
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
        $this->isDeleteModalOpen = false;
        $this->userIdToDelete = null;
    }

    private function resetInputFields()
    {
        $this->name = '';
        $this->email = '';
        $this->password = '';
        $this->password_confirmation = '';
        $this->user_id = '';
    }

    public function store()
    {
        $rules = [
            'name' => 'required|min:3',
            'email' => 'required|email|unique:users,email' . ($this->user_id ? ',' . $this->user_id : ''),
            'password' => $this->user_id ? 'nullable|min:6|confirmed' : 'required|min:6|confirmed',
        ];

        $messages = [
            'name.required' => 'O campo nome é obrigatório.',
            'name.min' => 'O nome deve ter pelo menos 3 caracteres.',
            'email.required' => 'O e-mail é obrigatório.',
            'email.email' => 'Informe um e-mail válido.',
            'email.unique' => 'Este e-mail já está sendo utilizado por outro usuário.',
            'password.required' => 'A senha é obrigatória.',
            'password.min' => 'A senha deve ter pelo menos 6 caracteres.',
            'password.confirmed' => 'A confirmação de senha não confere.',
        ];

        $this->validate($rules, $messages);

        $data = [
            'name' => $this->name,
            'email' => $this->email,
        ];

        if ($this->password) {
            $data['password'] = Hash::make($this->password);
        }

        User::updateOrCreate(['id' => $this->user_id], $data);

        session()->flash('message', $this->user_id ? 'Usuário atualizado com sucesso.' : 'Usuário criado com sucesso.');

        $this->closeModal();
        $this->resetInputFields();
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        $this->user_id = $id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->password = '';
        $this->openModal();
    }

    public function confirmDelete($id)
    {
        $this->userIdToDelete = $id;
        $this->isDeleteModalOpen = true;
    }

    public function delete()
    {
        if ($this->userIdToDelete == auth()->id()) {
            session()->flash('error', 'Você não pode excluir a si mesmo.');
            $this->closeModal();
            return;
        }

        User::find($this->userIdToDelete)->delete();
        session()->flash('message', 'Usuário excluído com sucesso.');
        $this->closeModal();
    }
};
?>

<div>
    <div class="search-bar">
        <input type="text" class="form-control" placeholder="Pesquisar usuários..." wire:model.live="search">
        <button wire:click="create()" class="btn btn-primary no-wrap">
            <i class="fas fa-plus"></i> Novo Usuário
        </button>
    </div>

    @if (session()->has('message'))
        <div class="alert-success">
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="error-message">
            {{ session('error') }}
        </div>
    @endif

    <div class="card">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>E-mail</th>
                        <th style="width: 150px;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>{{ $user->id }}</td>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td class="action-btns">
                                <button wire:click="edit({{ $user->id }})" class="btn btn-secondary btn-sm"
                                    title="Editar Usuário">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button wire:click="confirmDelete({{ $user->id }})" class="btn btn-danger btn-sm"
                                    title="Excluir Usuário">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="empty-state">
                                Nenhum usuário encontrado.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="pagination-wrapper">
            {{ $users->links('vendor.pagination.custom') }}
        </div>
    </div>

    @if($isModalOpen)
        <div class="modal-backdrop">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title">{{ $user_id ? 'Editar Usuário' : 'Novo Usuário' }}</h2>
                    <button wire:click="closeModal()" class="btn-icon">&times;</button>
                </div>
                <form wire:submit.prevent="store()" autocomplete="off">
                    <div class="form-group">
                        <label for="name">Nome</label>
                        <input type="text" class="form-control" id="name" wire:model="name" autocomplete="off">
                        @error('name') <span class="text-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="email">E-mail</label>
                        <input type="email" class="form-control" id="email" wire:model="email" autocomplete="off">
                        @error('email') <span class="text-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="password">Senha {{ $user_id ? '(Deixe em branco para não alterar)' : '' }}</label>
                        <input type="password" class="form-control" id="password" wire:model="password"
                            autocomplete="new-password">
                        @error('password') <span class="text-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="password_confirmation">Confirmar Senha</label>
                        <input type="password" class="form-control" id="password_confirmation"
                            wire:model="password_confirmation" autocomplete="new-password">
                    </div>
                    <div class="modal-footer">
                        <button type="button" wire:click="closeModal()" class="btn btn-secondary">Cancelar</button>
                        <button type="submit" class="btn btn-primary">{{ $user_id ? 'Atualizar' : 'Salvar' }}</button>
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
                    Tem certeza que deseja excluir este usuário? Esta ação não pode ser desfeita.
                </div>
                <div class="modal-footer">
                    <button type="button" wire:click="closeModal()" class="btn btn-secondary">Cancelar</button>
                    <button type="button" wire:click="delete()" class="btn btn-danger">Sim, Excluir</button>
                </div>
            </div>
        </div>
    @endif
</div>