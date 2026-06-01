@extends('layouts.app')

@section('page-title', 'Gerenciar Usuários')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Usuários</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm mb-4">
                <div class="card-header border-0 bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center w-100">
                        <h5 class="mb-0 text-secondary">
                            <i class="fa-solid fa-users me-2 text-primary"></i>
                            Lista de Usuários
                        </h5>                       
                        <div class="d-flex align-items-center gap-3">
                            <!-- Alternador Tabela / Cards -->
                            <div class="view-mode-toggle-wrapper">
                                <button type="button" class="view-mode-btn" id="viewModeTableBtn" title="Visualização em Tabela">
                                    <i class="fa-solid fa-list-ul"></i>
                                </button>
                                <button type="button" class="view-mode-btn" id="viewModeCardBtn" title="Visualização em Cards">
                                    <i class="fa-solid fa-table-cells-large"></i>
                                </button>
                            </div>
                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#createUserModal">
                                <i class="fa-solid fa-user-plus me-1"></i> Novo Usuário
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($users->isEmpty())
                        <div class="text-center p-5">
                            <i class="fa-solid fa-users fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-0">Nenhum usuário cadastrado no sistema.</p>
                        </div>
                    @else
                        <!-- MODO TABELA -->
                        <div id="view-table-container" class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Nome</th>
                                        <th>E-mail</th>
                                        <th>Perfil (Role)</th>
                                        <th>Vínculo Corporativo</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-end px-4" style="width: 200px;">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($users as $user)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="{{ $user->profile_photo_path ? asset('storage/' . $user->profile_photo_path) : 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($user->email))) . '?d=mp&s=40' }}" 
                                                         class="rounded-circle me-2" alt="Avatar" style="width: 40px; height: 40px; object-fit: cover;">
                                                    <strong>{{ $user->name }}</strong>
                                                </div>
                                            </td>
                                            <td>{{ $user->email }}</td>
                                            <td>
                                                @switch($user->role)
                                                    @case('super_admin')
                                                        <span class="badge bg-danger">Administrador Global</span>
                                                        @break
                                                    @case('gestor')
                                                        <span class="badge bg-primary">Gestor</span>
                                                        @break
                                                    @case('fornecedor')
                                                        <span class="badge bg-success">Fornecedor</span>
                                                        @break
                                                @endswitch
                                            </td>
                                            <td>
                                                @if($user->isSuperAdmin())
                                                    <span class="text-muted">Acesso Global</span>
                                                @elseif($user->isGestor())
                                                    <div class="fw-bold text-secondary fs-7">
                                                        Empresa Ativa: {{ $user->company->name ?? 'N/A' }}
                                                    </div>
                                                    <span class="text-muted fs-8">
                                                        Empresas: {{ $user->companies->pluck('name')->implode(', ') ?: 'Nenhuma' }}
                                                    </span>
                                                @elseif($user->isFornecedor())
                                                    <div class="fw-bold text-secondary fs-7">
                                                        Provedor: {{ $user->provider->name ?? 'N/A' }}
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if($user->id !== auth()->id())
                                                    <form action="{{ route('users.toggle', $user) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('PATCH')
                                                        <div class="form-check form-switch d-inline-block align-middle">
                                                            <input class="form-check-input" type="checkbox" role="switch" onChange="this.form.submit()" {{ $user->active ? 'checked' : '' }}>
                                                            <label class="form-check-label fw-bold text-secondary fs-7 ms-1">{{ $user->active ? 'Ativo' : 'Inativo' }}</label>
                                                        </div>
                                                    </form>
                                                @else
                                                    <div class="form-check form-switch d-inline-block align-middle">
                                                        <input class="form-check-input" type="checkbox" role="switch" checked disabled>
                                                        <label class="form-check-label fw-bold text-secondary fs-7 ms-1">Ativo</label>
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="text-end px-4">
                                                <div class="d-flex justify-content-end gap-2">
                                                    <!-- Edit Button -->
                                                    <button type="button" class="btn btn-sm btn-primary btn-edit-user"
                                                            data-id="{{ $user->id }}"
                                                            data-name="{{ $user->name }}"
                                                            data-email="{{ $user->email }}"
                                                            data-role="{{ $user->role }}"
                                                            data-company-id="{{ $user->company_id }}"
                                                            data-provider-id="{{ $user->provider_id }}"
                                                            data-companies="{{ $user->companies->pluck('id')->toJson() }}"
                                                            data-url="{{ route('users.update', $user) }}">
                                                        <i class="fa-solid fa-pen-to-square"></i> Editar
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- MODO CARDS -->
                        <div id="view-card-container" class="p-4 d-none">
                            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-3">
                                @foreach($users as $user)
                                    <div class="col">
                                        <div class="card h-100 shadow-sm border border-light">
                                            <div class="card-body">
                                                <div class="d-flex align-items-center mb-3">
                                                    <img src="{{ $user->profile_photo_path ? asset('storage/' . $user->profile_photo_path) : 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($user->email))) . '?d=mp&s=40' }}" 
                                                         class="rounded-circle me-3" alt="Avatar" style="width: 48px; height: 48px; object-fit: cover;">
                                                    <div>
                                                        <h6 class="fw-bold mb-0 text-dark">{{ $user->name }}</h6>
                                                        <small class="text-muted">ID: {{ $user->id }}</small>
                                                    </div>
                                                </div>
                                                <p class="mb-2 fs-7 text-secondary">
                                                    <strong>E-mail:</strong> {{ $user->email }}
                                                </p>
                                                <div class="mb-2 fs-7">
                                                    <strong>Perfil:</strong>
                                                    @switch($user->role)
                                                        @case('super_admin')
                                                            <span class="badge bg-danger text-white">Administrador Global</span>
                                                            @break
                                                        @case('gestor')
                                                            <span class="badge bg-primary text-white">Gestor</span>
                                                            @break
                                                        @case('fornecedor')
                                                            <span class="badge bg-success text-white">Fornecedor</span>
                                                            @break
                                                    @endswitch
                                                </div>
                                                <div class="mb-3 fs-7 text-secondary border-top pt-2 mt-2">
                                                    <strong>Vínculo:</strong>
                                                    @if($user->isSuperAdmin())
                                                        <span class="text-muted">Acesso Global</span>
                                                    @elseif($user->isGestor())
                                                        <span class="text-muted d-block fs-8">Empresa Principal: {{ $user->company->name ?? 'N/A' }}</span>
                                                        <span class="text-muted d-block fs-8">Empresas: {{ $user->companies->pluck('name')->implode(', ') ?: 'Nenhuma' }}</span>
                                                    @elseif($user->isFornecedor())
                                                        <span class="text-muted d-block fs-8">Provedor: {{ $user->provider->name ?? 'N/A' }}</span>
                                                    @endif
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center border-top pt-3">
                                                    @if($user->id !== auth()->id())
                                                        <form action="{{ route('users.toggle', $user) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            @method('PATCH')
                                                            <div class="form-check form-switch align-middle mb-0">
                                                                <input class="form-check-input" type="checkbox" role="switch" onChange="this.form.submit()" {{ $user->active ? 'checked' : '' }}>
                                                                <label class="form-check-label fw-bold text-secondary fs-8 ms-1">{{ $user->active ? 'Ativo' : 'Inativo' }}</label>
                                                            </div>
                                                        </form>
                                                    @else
                                                        <div class="form-check form-switch align-middle mb-0">
                                                            <input class="form-check-input" type="checkbox" role="switch" checked disabled>
                                                            <label class="form-check-label fw-bold text-secondary fs-8 ms-1">Ativo</label>
                                                        </div>
                                                    @endif
                                                    <button type="button" class="btn btn-xs btn-primary btn-edit-user"
                                                            data-id="{{ $user->id }}"
                                                            data-name="{{ $user->name }}"
                                                            data-email="{{ $user->email }}"
                                                            data-role="{{ $user->role }}"
                                                            data-company-id="{{ $user->company_id }}"
                                                            data-provider-id="{{ $user->provider_id }}"
                                                            data-companies="{{ $user->companies->pluck('id')->toJson() }}"
                                                            data-url="{{ route('users.update', $user) }}">
                                                        <i class="fa-solid fa-pen-to-square me-1"></i> Editar
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL DE CADASTRO (CREATE) -->
    <div class="modal fade" id="createUserModal" tabindex="-1" aria-labelledby="createUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('users.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="createUserModalLabel">Novo Usuário</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Nome -->
                        <div class="mb-3">
                            <label for="create_name" class="form-label fw-bold">Nome completo:</label>
                            <input type="text" name="name" id="create_name" class="form-control" value="{{ old('name') }}" required placeholder="Ex: Rodrigo Lima">
                        </div>

                        <!-- E-mail -->
                        <div class="mb-3">
                            <label for="create_email" class="form-label fw-bold">E-mail de Acesso:</label>
                            <input type="email" name="email" id="create_email" class="form-control" value="{{ old('email') }}" required placeholder="Ex: rodrigo@empresa.com">
                        </div>

                        <!-- Senha -->
                        <div class="mb-3">
                            <label for="create_password" class="form-label fw-bold">Senha Inicial:</label>
                            <input type="password" name="password" id="create_password" class="form-control" required placeholder="Mínimo de 8 caracteres">
                        </div>

                        <!-- Perfil (Role) -->
                        <div class="mb-3">
                            <label for="create_role" class="form-label fw-bold">Nível de Acesso (Perfil):</label>
                            <select name="role" id="create_role" class="form-select" required>
                                <option value="">Selecione o perfil...</option>
                                @if(auth()->user()->isSuperAdmin())
                                    <option value="super_admin" {{ old('role') === 'super_admin' ? 'selected' : '' }}>Administrador Global</option>
                                @endif
                                <option value="gestor" {{ old('role') === 'gestor' ? 'selected' : '' }}>Gestor</option>
                                <option value="fornecedor" {{ old('role') === 'fornecedor' ? 'selected' : '' }}>Fornecedor</option>
                            </select>
                        </div>

                        <!-- Seleção Condicional de Empresa (Ativa/Padrão) -->
                        <div class="mb-3 d-none" id="create-company-section">
                            <label for="create_company_id" class="form-label fw-bold">Empresa Principal:</label>
                            <select name="company_id" id="create_company_id" class="form-select">
                                <option value="">Selecione a empresa...</option>
                                @foreach($companies as $company)
                                    <option value="{{ $company->id }}" {{ old('company_id') == $company->id ? 'selected' : '' }}>
                                        {{ $company->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Checklist de Empresas associadas (Múltiplas Empresas para Gestor) -->
                        <div class="card bg-light border-0 mb-3 d-none" id="create-companies-list-section">
                            <div class="card-body py-2">
                                <label class="form-label fw-bold mb-2">Vincular Empresas de Gestão:</label>
                                <div class="row">
                                    @foreach($companies as $company)
                                        <div class="col-12 mb-1">
                                            <div class="form-check">
                                                <input class="form-check-input check-create-company" type="checkbox" name="companies[]" value="{{ $company->id }}" id="create_companies_{{ $company->id }}">
                                                <label class="form-check-label fs-7" for="create_companies_{{ $company->id }}">
                                                    {{ $company->name }}
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <!-- Seleção Condicional de Fornecedor -->
                        <div class="mb-3 d-none" id="create-provider-section">
                            <label for="create_provider_id" class="form-label fw-bold">Fornecedor Associado:</label>
                            <select name="provider_id" id="create_provider_id" class="form-select">
                                <option value="">Selecione o fornecedor...</option>
                                @foreach($providers as $provider)
                                    <option value="{{ $provider->id }}" {{ old('provider_id') == $provider->id ? 'selected' : '' }}>
                                        {{ $provider->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        @if($errors->any() && !old('_method'))
                            <div class="alert alert-danger py-2 mt-2 mb-0">
                                <ul class="mb-0 fs-7">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save me-1"></i> Cadastrar Usuário</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL DE EDIÇÃO (EDIT) -->
    <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="form-edit-user" action="" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <!-- Campo escondido para rastrear o ID em caso de falha de validação -->
                    <input type="hidden" name="user_id" id="edit_user_id" value="{{ old('user_id') }}">

                    <div class="modal-header">
                        <h5 class="modal-title" id="editUserModalLabel">Editar Usuário</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Nome -->
                        <div class="mb-3">
                            <label for="edit_name" class="form-label fw-bold">Nome completo:</label>
                            <input type="text" name="name" id="edit_name" class="form-control" value="{{ old('name') }}" required>
                        </div>

                        <!-- E-mail -->
                        <div class="mb-3">
                            <label for="edit_email" class="form-label fw-bold">E-mail de Acesso:</label>
                            <input type="email" name="email" id="edit_email" class="form-control" value="{{ old('email') }}" required>
                        </div>

                        <!-- Senha -->
                        <div class="mb-3">
                            <label for="edit_password" class="form-label fw-bold">Senha (deixe em branco para manter a atual):</label>
                            <input type="password" name="password" id="edit_password" class="form-control" placeholder="Mínimo de 8 caracteres">
                        </div>

                        <!-- Perfil (Role) -->
                        <div class="mb-3">
                            <label for="edit_role" class="form-label fw-bold">Nível de Acesso (Perfil):</label>
                            <select name="role" id="edit_role" class="form-select" required>
                                @if(auth()->user()->isSuperAdmin())
                                    <option value="super_admin">Administrador Global</option>
                                @endif
                                <option value="gestor">Gestor</option>
                                <option value="fornecedor">Fornecedor</option>
                            </select>
                        </div>

                        <!-- Seleção Condicional de Empresa (Ativa/Padrão) -->
                        <div class="mb-3 d-none" id="edit-company-section">
                            <label for="edit_company_id" class="form-label fw-bold">Empresa Principal:</label>
                            <select name="company_id" id="edit_company_id" class="form-select">
                                <option value="">Selecione a empresa...</option>
                                @foreach($companies as $company)
                                    <option value="{{ $company->id }}">
                                        {{ $company->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Checklist de Empresas associadas (Múltiplas Empresas para Gestor) -->
                        <div class="card bg-light border-0 mb-3 d-none" id="edit-companies-list-section">
                            <div class="card-body py-2">
                                <label class="form-label fw-bold mb-2">Vincular Empresas de Gestão:</label>
                                <div class="row">
                                    @foreach($companies as $company)
                                        <div class="col-12 mb-1">
                                            <div class="form-check">
                                                <input class="form-check-input check-edit-company" type="checkbox" name="companies[]" value="{{ $company->id }}" id="edit_companies_{{ $company->id }}">
                                                <label class="form-check-label fs-7" for="edit_companies_{{ $company->id }}">
                                                    {{ $company->name }}
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <!-- Seleção Condicional de Fornecedor -->
                        <div class="mb-3 d-none" id="edit-provider-section">
                            <label for="edit_provider_id" class="form-label fw-bold">Fornecedor Associado:</label>
                            <select name="provider_id" id="edit_provider_id" class="form-select">
                                <option value="">Selecione o fornecedor...</option>
                                @foreach($providers as $provider)
                                    <option value="{{ $provider->id }}">
                                        {{ $provider->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        @if($errors->any() && old('_method') === 'PUT')
                            <div class="alert alert-danger py-2 mt-2 mb-0">
                                <ul class="mb-0 fs-7">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save me-1"></i> Salvar Alterações</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Modais Bootstrap
            const createModal = new bootstrap.Modal(document.getElementById('createUserModal'));
            const editModal = new bootstrap.Modal(document.getElementById('editUserModal'));

            // Elementos de Cadastro
            const createRoleSelect = document.getElementById('create_role');
            const createCompanySec = document.getElementById('create-company-section');
            const createCompaniesListSec = document.getElementById('create-companies-list-section');
            const createProviderSec = document.getElementById('create-provider-section');

            // Elementos de Edição
            const editForm = document.getElementById('form-edit-user');
            const editIdInput = document.getElementById('edit_user_id');
            const editNameInput = document.getElementById('edit_name');
            const editEmailInput = document.getElementById('edit_email');
            const editRoleSelect = document.getElementById('edit_role');
            const editCompanySec = document.getElementById('edit-company-section');
            const editCompaniesListSec = document.getElementById('edit-companies-list-section');
            const editProviderSec = document.getElementById('edit-provider-section');
            const editCompanySelect = document.getElementById('edit_company_id');
            const editProviderSelect = document.getElementById('edit_provider_id');

            // Função para controlar a exibição dos campos condicionais (Cadastro)
            function toggleCreateFields() {
                const role = createRoleSelect.value;
                if (role === 'gestor') {
                    createCompanySec.classList.remove('d-none');
                    createCompaniesListSec.classList.remove('d-none');
                    createProviderSec.classList.add('d-none');
                } else if (role === 'fornecedor') {
                    createCompanySec.classList.add('d-none');
                    createCompaniesListSec.classList.add('d-none');
                    createProviderSec.classList.remove('d-none');
                } else {
                    createCompanySec.classList.add('d-none');
                    createCompaniesListSec.classList.add('d-none');
                    createProviderSec.classList.add('d-none');
                }
            }

            // Função para controlar a exibição dos campos condicionais (Edição)
            function toggleEditFields() {
                const role = editRoleSelect.value;
                if (role === 'gestor') {
                    editCompanySec.classList.remove('d-none');
                    editCompaniesListSec.classList.remove('d-none');
                    editProviderSec.classList.add('d-none');
                } else if (role === 'fornecedor') {
                    editCompanySec.classList.add('d-none');
                    editCompaniesListSec.classList.add('d-none');
                    editProviderSec.classList.remove('d-none');
                } else {
                    editCompanySec.classList.add('d-none');
                    editCompaniesListSec.classList.add('d-none');
                    editProviderSec.classList.add('d-none');
                }
            }

            createRoleSelect.addEventListener('change', toggleCreateFields);
            editRoleSelect.addEventListener('change', toggleEditFields);

            // Ouvinte para o clique de Editar
            document.querySelectorAll('.btn-edit-user').forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const name = this.getAttribute('data-name');
                    const email = this.getAttribute('data-email');
                    const role = this.getAttribute('data-role');
                    const companyId = this.getAttribute('data-company-id');
                    const providerId = this.getAttribute('data-provider-id');
                    const associatedCompanies = JSON.parse(this.getAttribute('data-companies') || '[]');
                    const actionUrl = this.getAttribute('data-url');

                    // Preencher campos
                    editForm.setAttribute('action', actionUrl);
                    editIdInput.value = id;
                    editNameInput.value = name;
                    editEmailInput.value = email;
                    editRoleSelect.value = role;
                    editCompanySelect.value = companyId !== 'null' ? companyId : '';
                    editProviderSelect.value = providerId !== 'null' ? providerId : '';

                    // Resetar checkboxes de empresas na edição
                    document.querySelectorAll('.check-edit-company').forEach(checkbox => {
                        checkbox.checked = associatedCompanies.includes(parseInt(checkbox.value));
                    });

                    toggleEditFields();
                    editModal.show();
                });
            });

            // Tratamento de erros de validação da sessão
            @if($errors->any())
                @if(old('_method') === 'PUT')
                    const oldId = "{{ old('user_id') }}";
                    if (oldId) {
                        editForm.setAttribute('action', "/users/" + oldId);
                        editRoleSelect.value = "{{ old('role') }}";
                        editCompanySelect.value = "{{ old('company_id') }}";
                        editProviderSelect.value = "{{ old('provider_id') }}";

                        // Restaura checklist de empresas na edição
                        const oldCompanies = @json(old('companies') ?? []);
                        document.querySelectorAll('.check-edit-company').forEach(checkbox => {
                            checkbox.checked = oldCompanies.includes(checkbox.value);
                        });

                        toggleEditFields();
                        editModal.show();
                    }
                @else
                    createRoleSelect.value = "{{ old('role') }}";
                    toggleCreateFields();
                    createModal.show();
                @endif
            @endif

            // View Mode Toggle
            const toggleTableBtn = document.getElementById('viewModeTableBtn');
            const toggleCardBtn = document.getElementById('viewModeCardBtn');
            const tableContainer = document.getElementById('view-table-container');
            const cardContainer = document.getElementById('view-card-container');
            
            if (toggleTableBtn && toggleCardBtn && tableContainer && cardContainer) {
                const savedMode = localStorage.getItem('view_mode_users') || 'table';
                
                const setMode = (mode) => {
                    if (mode === 'card') {
                        tableContainer.classList.add('d-none');
                        cardContainer.classList.remove('d-none');
                        toggleTableBtn.classList.remove('active');
                        toggleCardBtn.classList.add('active');
                    } else {
                        tableContainer.classList.remove('d-none');
                        cardContainer.classList.add('d-none');
                        toggleTableBtn.classList.add('active');
                        toggleCardBtn.classList.remove('active');
                    }
                    localStorage.setItem('view_mode_users', mode);
                };
                
                setMode(savedMode);
                
                toggleTableBtn.addEventListener('click', () => setMode('table'));
                toggleCardBtn.addEventListener('click', () => setMode('card'));
            }
        });
    </script>
@endpush
