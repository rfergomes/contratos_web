@extends('layouts.app')

@section('page-title', 'Empresas Contratantes')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Empresas</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm mb-4">
                <div class="card-header border-0 bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-secondary">
                        <i class="fa-solid fa-building me-2 text-primary"></i>
                        Lista de Empresas
                    </h5>
                    <!-- Botão que abre o modal de criação -->
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#createCompanyModal">
                        <i class="fa-solid fa-plus me-1"></i> Nova Empresa
                    </button>
                </div>
                <div class="card-body p-0">
                    @if($companies->isEmpty())
                        <div class="text-center p-5">
                            <i class="fa-solid fa-building fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-0">Nenhuma empresa contratante cadastrada no sistema.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 80px;">ID</th>
                                        <th>Nome da Empresa</th>
                                        <th>CNPJ</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-end px-4" style="width: 200px;">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($companies as $company)
                                        <tr>
                                            <td>{{ $company->id }}</td>
                                            <td><strong>{{ $company->name }}</strong></td>
                                            <td>{{ $company->cnpj }}</td>
                                            <td class="text-center">
                                                @if($company->active)
                                                    <span class="badge bg-success">Ativa</span>
                                                @else
                                                    <span class="badge bg-secondary">Inativa</span>
                                                @endif
                                            </td>
                                            <td class="text-end px-4">
                                                <div class="d-flex justify-content-end gap-2">
                                                    <!-- Toggle Status -->
                                                    <form action="{{ route('companies.toggle', $company) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="btn btn-sm btn-outline-secondary" title="Alternar Ativo/Inativo">
                                                            <i class="fa-solid fa-power-off"></i>
                                                        </button>
                                                    </form>

                                                    <!-- Edit Button (Abre o modal via JS populando os dados) -->
                                                    <button type="button" class="btn btn-sm btn-primary btn-edit-company" 
                                                            data-id="{{ $company->id }}"
                                                            data-name="{{ $company->name }}"
                                                            data-cnpj="{{ $company->cnpj }}"
                                                            data-active="{{ $company->active ? '1' : '0' }}"
                                                            data-url="{{ route('companies.update', $company) }}">
                                                        <i class="fa-solid fa-pen-to-square"></i> Editar
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL DE CADASTRO (CREATE) -->
    <div class="modal fade" id="createCompanyModal" tabindex="-1" aria-labelledby="createCompanyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('companies.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="createCompanyModalLabel">Nova Empresa Contratante</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Nome -->
                        <div class="mb-3">
                            <label for="create_name" class="form-label fw-bold">Nome / Razão Social:</label>
                            <input type="text" name="name" id="create_name" class="form-control @if($errors->any() && !old('_method')) is-invalid @endif" value="{{ old('name') }}" placeholder="Nome da empresa contratante" required>
                            @if($errors->any() && !old('_method'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('name') ?: $errors->first('cnpj') }}</strong>
                                </span>
                            @endif
                        </div>

                        <!-- CNPJ -->
                        <div class="mb-3">
                            <label for="create_cnpj" class="form-label fw-bold">CNPJ:</label>
                            <input type="text" name="cnpj" id="create_cnpj" class="form-control @if($errors->any() && !old('_method')) is-invalid @endif" value="{{ old('cnpj') }}" placeholder="Ex: 00.000.000/0000-00" required>
                        </div>

                        <!-- Active Switch -->
                        <div class="form-check form-switch mt-3">
                            <input class="form-check-input" type="checkbox" name="active" id="create_active" value="1" checked>
                            <label class="form-check-label fw-bold" for="create_active">Empresa Ativa</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save me-1"></i> Salvar Empresa</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL DE EDIÇÃO (EDIT) -->
    <div class="modal fade" id="editCompanyModal" tabindex="-1" aria-labelledby="editCompanyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="form-edit-company" action="" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <!-- Campo escondido para rastrear o ID em caso de falha de validação -->
                    <input type="hidden" name="company_id" id="edit_company_id" value="{{ old('company_id') }}">

                    <div class="modal-header">
                        <h5 class="modal-title" id="editCompanyModalLabel">Editar Empresa Contratante</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Nome -->
                        <div class="mb-3">
                            <label for="edit_name" class="form-label fw-bold">Nome / Razão Social:</label>
                            <input type="text" name="name" id="edit_name" class="form-control @if($errors->any() && old('_method') === 'PUT') is-invalid @endif" value="{{ old('name') }}" required>
                            @if($errors->any() && old('_method') === 'PUT')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('name') ?: $errors->first('cnpj') }}</strong>
                                </span>
                            @endif
                        </div>

                        <!-- CNPJ -->
                        <div class="mb-3">
                            <label for="edit_cnpj" class="form-label fw-bold">CNPJ:</label>
                            <input type="text" name="cnpj" id="edit_cnpj" class="form-control @if($errors->any() && old('_method') === 'PUT') is-invalid @endif" value="{{ old('cnpj') }}" required>
                        </div>

                        <!-- Active Switch -->
                        <div class="form-check form-switch mt-3">
                            <input class="form-check-input" type="checkbox" name="active" id="edit_active" value="1">
                            <label class="form-check-label fw-bold" for="edit_active">Empresa Ativa</label>
                        </div>
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
            // Inicializar Modais
            const createModal = new bootstrap.Modal(document.getElementById('createCompanyModal'));
            const editModal = new bootstrap.Modal(document.getElementById('editCompanyModal'));
            
            const editForm = document.getElementById('form-edit-company');
            const editIdInput = document.getElementById('edit_company_id');
            const editNameInput = document.getElementById('edit_name');
            const editCnpjInput = document.getElementById('edit_cnpj');
            const editActiveInput = document.getElementById('edit_active');

            // Capturar clique no botão editar
            document.querySelectorAll('.btn-edit-company').forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const name = this.getAttribute('data-name');
                    const cnpj = this.getAttribute('data-cnpj');
                    const active = this.getAttribute('data-active');
                    const actionUrl = this.getAttribute('data-url');

                    // Preencher campos
                    editForm.setAttribute('action', actionUrl);
                    editIdInput.value = id;
                    editNameInput.value = name;
                    editCnpjInput.value = cnpj;
                    editActiveInput.checked = active === '1';

                    // Mostrar modal
                    editModal.show();
                });
            });

            // Recuperar modal aberto se houver erros de validação
            @if($errors->any())
                @if(old('_method') === 'PUT')
                    // Era uma edição, reconstrói a URL da rota utilizando o ID guardado
                    const oldId = "{{ old('company_id') }}";
                    if (oldId) {
                        editForm.setAttribute('action', "/companies/" + oldId);
                        editActiveInput.checked = "{{ old('active') }}" === '1';
                        editModal.show();
                    }
                @else
                    // Era um cadastro
                    createModal.show();
                @endif
            @endif
        });
    </script>
@endpush
