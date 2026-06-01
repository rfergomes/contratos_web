@extends('layouts.app')

@section('page-title', 'Empresas Contratantes')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Empresas</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm mb-4">
                <div class="card-header border-0 bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center w-100">
                        <h5 class="mb-0 text-secondary">
                            <i class="fa-solid fa-building me-2 text-primary"></i>
                            Lista de Empresas
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
                            <!-- Botão Nova Empresa -->
                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#createCompanyModal">
                                <i class="fa-solid fa-plus me-1"></i> Nova Empresa
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($companies->isEmpty())
                        <div class="text-center p-5">
                            <i class="fa-solid fa-building fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-0">Nenhuma empresa contratante cadastrada no sistema.</p>
                        </div>
                    @else
                        <!-- MODO TABELA -->
                        <div id="view-table-container" class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 80px;">ID</th>
                                        <th>Nome da Empresa</th>
                                        <th>CNPJ</th>
                                        <th class="text-center" style="width: 150px;">Status</th>
                                        <th class="text-end px-4" style="width: 150px;">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($companies as $company)
                                        <tr>
                                            <td>{{ $company->id }}</td>
                                            <td><strong>{{ $company->name }}</strong></td>
                                            <td>{{ $company->cnpj }}</td>
                                            <td class="text-center">
                                                <form action="{{ route('companies.toggle', $company) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <div class="form-check form-switch d-inline-block align-middle">
                                                        <input class="form-check-input" type="checkbox" role="switch" onChange="this.form.submit()" {{ $company->active ? 'checked' : '' }}>
                                                        <label class="form-check-label fw-bold text-secondary fs-7 ms-1">{{ $company->active ? 'Ativa' : 'Inativa' }}</label>
                                                    </div>
                                                </form>
                                            </td>
                                            <td class="text-end px-4">
                                                <button type="button" class="btn btn-sm btn-primary btn-edit-company" 
                                                        data-id="{{ $company->id }}"
                                                        data-name="{{ $company->name }}"
                                                        data-cnpj="{{ $company->cnpj }}"
                                                        data-active="{{ $company->active ? '1' : '0' }}"
                                                        data-url="{{ route('companies.update', $company) }}">
                                                    <i class="fa-solid fa-pen-to-square"></i> Editar
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- MODO CARDS -->
                        <div id="view-card-container" class="p-4 d-none">
                            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-3">
                                @foreach($companies as $company)
                                    <div class="col">
                                        <div class="card h-100 shadow-sm border border-light">
                                            <div class="card-body">
                                                <div class="d-flex align-items-center mb-3">
                                                    <div class="bg-primary-subtle text-primary rounded p-2 me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                        <i class="fa-solid fa-building fs-5"></i>
                                                    </div>
                                                    <div>
                                                        <h6 class="fw-bold mb-0 text-dark">{{ $company->name }}</h6>
                                                        <small class="text-muted">ID: {{ $company->id }}</small>
                                                    </div>
                                                </div>
                                                <p class="mb-3 fs-7 text-secondary">
                                                    <strong>CNPJ:</strong> {{ $company->cnpj }}
                                                </p>
                                                <div class="d-flex justify-content-between align-items-center border-top pt-3">
                                                    <form action="{{ route('companies.toggle', $company) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('PATCH')
                                                        <div class="form-check form-switch align-middle mb-0">
                                                            <input class="form-check-input" type="checkbox" role="switch" onChange="this.form.submit()" {{ $company->active ? 'checked' : '' }}>
                                                            <label class="form-check-label fw-bold text-secondary fs-8 ms-1">{{ $company->active ? 'Ativa' : 'Inativa' }}</label>
                                                        </div>
                                                    </form>
                                                    <button type="button" class="btn btn-xs btn-primary btn-edit-company"
                                                            data-id="{{ $company->id }}"
                                                            data-name="{{ $company->name }}"
                                                            data-cnpj="{{ $company->cnpj }}"
                                                            data-active="{{ $company->active ? '1' : '0' }}"
                                                            data-url="{{ route('companies.update', $company) }}">
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

            // View Mode Toggle
            const toggleTableBtn = document.getElementById('viewModeTableBtn');
            const toggleCardBtn = document.getElementById('viewModeCardBtn');
            const tableContainer = document.getElementById('view-table-container');
            const cardContainer = document.getElementById('view-card-container');
            
            if (toggleTableBtn && toggleCardBtn && tableContainer && cardContainer) {
                const savedMode = localStorage.getItem('view_mode_companies') || 'table';
                
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
                    localStorage.setItem('view_mode_companies', mode);
                };
                
                setMode(savedMode);
                
                toggleTableBtn.addEventListener('click', () => setMode('table'));
                toggleCardBtn.addEventListener('click', () => setMode('card'));
            }
        });
    </script>
@endpush

