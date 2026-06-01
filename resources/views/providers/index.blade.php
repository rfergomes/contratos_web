@extends('layouts.app')

@section('page-title', 'Fornecedores de Serviços')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Fornecedores</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm mb-4">
                <div class="card-header border-0 bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-secondary">
                        <i class="fa-solid fa-truck me-2 text-primary"></i>
                        Lista de Fornecedores
                    </h5>
                    @if(!auth()->user()->isFornecedor())
                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#createProviderModal">
                            <i class="fa-solid fa-plus me-1"></i> Novo Fornecedor
                        </button>
                    @endif
                </div>
                <div class="card-body p-0">
                    @if($providers->isEmpty())
                        <div class="text-center p-5">
                            <i class="fa-solid fa-truck fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-0">Nenhum fornecedor cadastrado no sistema.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 80px;">ID</th>
                                        <th>Razão Social / Nome</th>
                                        <th>CNPJ</th>
                                        <th>E-mail</th>
                                        <th>Telefone</th>
                                        <th class="text-center">Status</th>
                                        @if(!auth()->user()->isFornecedor())
                                            <th class="text-end px-4" style="width: 200px;">Ações</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($providers as $provider)
                                        <tr>
                                            <td>{{ $provider->id }}</td>
                                            <td><strong>{{ $provider->name }}</strong></td>
                                            <td>{{ $provider->cnpj }}</td>
                                            <td>{{ $provider->email ?? 'N/A' }}</td>
                                            <td>{{ $provider->phone ?? 'N/A' }}</td>
                                            <td class="text-center">
                                                @if($provider->active)
                                                    <span class="badge bg-success">Ativo</span>
                                                @else
                                                    <span class="badge bg-secondary">Inativo</span>
                                                @endif
                                            </td>
                                            @if(!auth()->user()->isFornecedor())
                                                <td class="text-end px-4">
                                                    <div class="d-flex justify-content-end gap-2">
                                                        <!-- Toggle Status -->
                                                        <form action="{{ route('providers.toggle', $provider) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            @method('PATCH')
                                                            <button type="submit" class="btn btn-sm btn-outline-secondary" title="Alternar Ativo/Inativo">
                                                                <i class="fa-solid fa-power-off"></i>
                                                            </button>
                                                        </form>

                                                        <!-- Edit Button (Abre o modal via JS populando os dados) -->
                                                        <button type="button" class="btn btn-sm btn-primary btn-edit-provider" 
                                                                data-id="{{ $provider->id }}"
                                                                data-name="{{ $provider->name }}"
                                                                data-cnpj="{{ $provider->cnpj }}"
                                                                data-email="{{ $provider->email }}"
                                                                data-phone="{{ $provider->phone }}"
                                                                data-url="{{ route('providers.update', $provider) }}">
                                                            <i class="fa-solid fa-pen-to-square"></i> Editar
                                                        </button>
                                                    </div>
                                                </td>
                                            @endif
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
    <div class="modal fade" id="createProviderModal" tabindex="-1" aria-labelledby="createProviderModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('providers.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="createProviderModalLabel">Novo Fornecedor de Serviços</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Nome -->
                        <div class="mb-3">
                            <label for="create_name" class="form-label fw-bold">Razão Social / Nome:</label>
                            <input type="text" name="name" id="create_name" class="form-control @if($errors->any() && !old('_method')) is-invalid @endif" value="{{ old('name') }}" placeholder="Nome do fornecedor" required>
                            @if($errors->any() && !old('_method'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('name') ?: ($errors->first('cnpj') ?: ($errors->first('email') ?: $errors->first('phone'))) }}</strong>
                                </span>
                            @endif
                        </div>

                        <!-- CNPJ -->
                        <div class="mb-3">
                            <label for="create_cnpj" class="form-label fw-bold">CNPJ:</label>
                            <input type="text" name="cnpj" id="create_cnpj" class="form-control @if($errors->any() && !old('_method')) is-invalid @endif" value="{{ old('cnpj') }}" placeholder="Ex: 00.000.000/0000-00" required>
                        </div>

                        <!-- Telefone e E-mail -->
                        <div class="row">
                            <div class="col-md-6 col-12 mb-3">
                                <label for="create_phone" class="form-label fw-bold">Telefone:</label>
                                <input type="text" name="phone" id="create_phone" class="form-control" value="{{ old('phone') }}" placeholder="Ex: (19) 99999-9999">
                            </div>
                            <div class="col-md-6 col-12 mb-3">
                                <label for="create_email" class="form-label fw-bold">E-mail:</label>
                                <input type="email" name="email" id="create_email" class="form-control" value="{{ old('email') }}" placeholder="comercial@provedor.com">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save me-1"></i> Salvar Fornecedor</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL DE EDIÇÃO (EDIT) -->
    <div class="modal fade" id="editProviderModal" tabindex="-1" aria-labelledby="editProviderModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="form-edit-provider" action="" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <!-- Campo escondido para rastrear o ID em caso de falha de validação -->
                    <input type="hidden" name="provider_id" id="edit_provider_id" value="{{ old('provider_id') }}">

                    <div class="modal-header">
                        <h5 class="modal-title" id="editProviderModalLabel">Editar Fornecedor</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Nome -->
                        <div class="mb-3">
                            <label for="edit_name" class="form-label fw-bold">Razão Social / Nome:</label>
                            <input type="text" name="name" id="edit_name" class="form-control @if($errors->any() && old('_method') === 'PUT') is-invalid @endif" value="{{ old('name') }}" required>
                            @if($errors->any() && old('_method') === 'PUT')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('name') ?: ($errors->first('cnpj') ?: ($errors->first('email') ?: $errors->first('phone'))) }}</strong>
                                </span>
                            @endif
                        </div>

                        <!-- CNPJ -->
                        <div class="mb-3">
                            <label for="edit_cnpj" class="form-label fw-bold">CNPJ:</label>
                            <input type="text" name="cnpj" id="edit_cnpj" class="form-control @if($errors->any() && old('_method') === 'PUT') is-invalid @endif" value="{{ old('cnpj') }}" required>
                        </div>

                        <!-- Telefone e E-mail -->
                        <div class="row">
                            <div class="col-md-6 col-12 mb-3">
                                <label for="edit_phone" class="form-label fw-bold">Telefone:</label>
                                <input type="text" name="phone" id="edit_phone" class="form-control" value="{{ old('phone') }}">
                            </div>
                            <div class="col-md-6 col-12 mb-3">
                                <label for="edit_email" class="form-label fw-bold">E-mail:</label>
                                <input type="email" name="email" id="edit_email" class="form-control" value="{{ old('email') }}">
                            </div>
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
            const createModal = new bootstrap.Modal(document.getElementById('createProviderModal'));
            const editModal = new bootstrap.Modal(document.getElementById('editProviderModal'));
            
            const editForm = document.getElementById('form-edit-provider');
            const editIdInput = document.getElementById('edit_provider_id');
            const editNameInput = document.getElementById('edit_name');
            const editCnpjInput = document.getElementById('edit_cnpj');
            const editPhoneInput = document.getElementById('edit_phone');
            const editEmailInput = document.getElementById('edit_email');

            // Capturar clique no botão editar
            document.querySelectorAll('.btn-edit-provider').forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const name = this.getAttribute('data-name');
                    const cnpj = this.getAttribute('data-cnpj');
                    const email = this.getAttribute('data-email');
                    const phone = this.getAttribute('data-phone');
                    const actionUrl = this.getAttribute('data-url');

                    // Preencher campos
                    editForm.setAttribute('action', actionUrl);
                    editIdInput.value = id;
                    editNameInput.value = name;
                    editCnpjInput.value = cnpj;
                    editPhoneInput.value = phone !== 'null' ? phone : '';
                    editEmailInput.value = email !== 'null' ? email : '';

                    // Mostrar modal
                    editModal.show();
                });
            });

            // Recuperar modal aberto se houver erros de validação
            @if($errors->any())
                @if(old('_method') === 'PUT')
                    const oldId = "{{ old('provider_id') }}";
                    if (oldId) {
                        editForm.setAttribute('action', "/providers/" + oldId);
                        editModal.show();
                    }
                @else
                    createModal.show();
                @endif
            @endif
        });
    </script>
@endpush
