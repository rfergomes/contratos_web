@extends('layouts.app')

@section('page-title', 'Fornecedores de Serviços')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Fornecedores</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm mb-4">
                <div class="card-header border-0 bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center w-100">
                        <h5 class="mb-0 text-secondary">
                            <i class="fa-solid fa-truck me-2 text-primary"></i>
                            Lista de Fornecedores
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
                            @if(!auth()->user()->isFornecedor())
                                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#createProviderModal">
                                    <i class="fa-solid fa-plus me-1"></i> Novo Fornecedor
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($providers->isEmpty())
                        <div class="text-center p-5">
                            <i class="fa-solid fa-truck fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-0">Nenhum fornecedor cadastrado no sistema.</p>
                        </div>
                    @else
                        <!-- MODO TABELA -->
                        <div id="view-table-container" class="table-responsive">
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
                                                <form action="{{ route('providers.toggle', $provider) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <div class="form-check form-switch d-inline-block align-middle">
                                                        <input class="form-check-input" type="checkbox" role="switch" onChange="this.form.submit()" {{ $provider->active ? 'checked' : '' }} {{ auth()->user()->isFornecedor() ? 'disabled' : '' }}>
                                                        <label class="form-check-label fw-bold text-secondary fs-7 ms-1">{{ $provider->active ? 'Ativo' : 'Inativo' }}</label>
                                                    </div>
                                                </form>
                                            </td>
                                            @if(!auth()->user()->isFornecedor())
                                                <td class="text-end px-4">
                                                    <div class="d-flex justify-content-end gap-2">
                                                        <!-- Contacts Button -->
                                                        <button type="button" class="btn btn-sm btn-outline-primary btn-contacts-provider" 
                                                                data-id="{{ $provider->id }}"
                                                                data-name="{{ $provider->name }}">
                                                            <i class="fa-solid fa-users"></i> Contatos
                                                        </button>

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

                        <!-- MODO CARDS -->
                        <div id="view-card-container" class="p-4 d-none">
                            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-3">
                                @foreach($providers as $provider)
                                    <div class="col">
                                        <div class="card h-100 shadow-sm border border-light">
                                            <div class="card-body">
                                                <div class="d-flex align-items-center mb-3">
                                                    <div class="bg-primary-subtle text-primary rounded p-2 me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                        <i class="fa-solid fa-truck fs-5"></i>
                                                    </div>
                                                    <div>
                                                        <h6 class="fw-bold mb-0 text-dark">{{ $provider->name }}</h6>
                                                        <small class="text-muted">ID: {{ $provider->id }}</small>
                                                    </div>
                                                </div>
                                                <p class="mb-2 fs-7 text-secondary">
                                                    <strong>CNPJ:</strong> {{ $provider->cnpj }}
                                                </p>
                                                <p class="mb-2 fs-7 text-secondary">
                                                    <strong>E-mail:</strong> {{ $provider->email ?? 'N/A' }}
                                                </p>
                                                <p class="mb-3 fs-7 text-secondary">
                                                    <strong>Telefone:</strong> {{ $provider->phone ?? 'N/A' }}
                                                </p>
                                                <div class="d-flex justify-content-between align-items-center border-top pt-3">
                                                    <form action="{{ route('providers.toggle', $provider) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('PATCH')
                                                        <div class="form-check form-switch align-middle mb-0">
                                                            <input class="form-check-input" type="checkbox" role="switch" onChange="this.form.submit()" {{ $provider->active ? 'checked' : '' }} {{ auth()->user()->isFornecedor() ? 'disabled' : '' }}>
                                                            <label class="form-check-label fw-bold text-secondary fs-8 ms-1">{{ $provider->active ? 'Ativo' : 'Inativo' }}</label>
                                                        </div>
                                                    </form>
                                                    <div class="d-flex gap-2">
                                                        <button type="button" class="btn btn-xs btn-outline-primary btn-contacts-provider" 
                                                                data-id="{{ $provider->id }}"
                                                                data-name="{{ $provider->name }}">
                                                            <i class="fa-solid fa-users"></i>
                                                        </button>
                                                        @if(!auth()->user()->isFornecedor())
                                                            <button type="button" class="btn btn-xs btn-primary btn-edit-provider"
                                                                    data-id="{{ $provider->id }}"
                                                                    data-name="{{ $provider->name }}"
                                                                    data-cnpj="{{ $provider->cnpj }}"
                                                                    data-email="{{ $provider->email }}"
                                                                    data-phone="{{ $provider->phone }}"
                                                                    data-url="{{ route('providers.update', $provider) }}">
                                                                <i class="fa-solid fa-pen-to-square me-1"></i> Editar
                                                            </button>
                                                        @endif
                                                    </div>
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

    <!-- MODAL DE CONTATOS (GERENCIAMENTO VIA AJAX) -->
    <div class="modal fade" id="manageContactsModal" tabindex="-1" aria-labelledby="manageContactsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="manageContactsModalLabel">
                        <i class="fa-solid fa-users text-primary me-2"></i>
                        Contatos de: <strong id="modal-provider-name"></strong>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <!-- Formulário de Adicionar/Editar Contato (Inline / Compacto) -->
                    <form id="form-contact-ajax" class="bg-light p-3 rounded mb-4 border">
                        @csrf
                        <input type="hidden" id="contact_id_ajax" value="">
                        <h6 class="fw-bold mb-3 text-secondary" id="contact-form-title">
                            <i class="fa-solid fa-user-plus me-1 text-success"></i> Novo Contato
                        </h6>
                        <div class="row g-2">
                            <div class="col-md-4 col-12">
                                <input type="text" id="contact_name_ajax" class="form-control form-control-sm" placeholder="Nome Completo" required>
                            </div>
                            <div class="col-md-3 col-12">
                                <input type="text" id="contact_phone_ajax" class="form-control form-control-sm" placeholder="Telefone">
                            </div>
                            <div class="col-md-3 col-12">
                                <input type="email" id="contact_email_ajax" class="form-control form-control-sm" placeholder="E-mail">
                            </div>
                            <div class="col-md-2 col-12 d-flex align-items-center">
                                <div class="form-check fs-8">
                                    <input type="checkbox" id="contact_is_main_ajax" class="form-check-input" value="1">
                                    <label for="contact_is_main_ajax" class="form-check-label fw-bold">Principal</label>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3 d-flex justify-content-end gap-2">
                            <button type="button" id="btn-cancel-contact-edit" class="btn btn-xs btn-secondary d-none">Cancelar Edição</button>
                            <button type="submit" id="btn-submit-contact" class="btn btn-xs btn-success"><i class="fa-solid fa-save me-1"></i> Salvar Contato</button>
                        </div>
                    </form>

                    <!-- Tabela de Contatos -->
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0 fs-7" id="table-contacts-ajax">
                            <thead class="table-light">
                                <tr>
                                    <th>Nome</th>
                                    <th>Telefone</th>
                                    <th>E-mail</th>
                                    <th class="text-center" style="width: 100px;">Principal</th>
                                    <th class="text-end" style="width: 120px;">Ações</th>
                                </tr>
                            </thead>
                            <tbody id="contacts-list-container">
                                <!-- Preenchido dinamicamente por JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Inicializar Modais de Fornecedores
            const createModal = new bootstrap.Modal(document.getElementById('createProviderModal'));
            const editModal = new bootstrap.Modal(document.getElementById('editProviderModal'));
            
            const editForm = document.getElementById('form-edit-provider');
            const editIdInput = document.getElementById('edit_provider_id');
            const editNameInput = document.getElementById('edit_name');
            const editCnpjInput = document.getElementById('edit_cnpj');
            const editPhoneInput = document.getElementById('edit_phone');
            const editEmailInput = document.getElementById('edit_email');

            // Capturar clique no botão editar fornecedor
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

            // Recuperar modal de fornecedor aberto se houver erros de validação
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

            // ================= AJAX Contatos de Fornecedores =================
            const contactsModal = new bootstrap.Modal(document.getElementById('manageContactsModal'));
            const providerNameSpan = document.getElementById('modal-provider-name');
            const contactsListContainer = document.getElementById('contacts-list-container');
            const contactForm = document.getElementById('form-contact-ajax');
            
            const contactIdInput = document.getElementById('contact_id_ajax');
            const contactNameInput = document.getElementById('contact_name_ajax');
            const contactPhoneInput = document.getElementById('contact_phone_ajax');
            const contactEmailInput = document.getElementById('contact_email_ajax');
            const contactIsMainCheckbox = document.getElementById('contact_is_main_ajax');
            
            const contactFormTitle = document.getElementById('contact-form-title');
            const btnCancelEdit = document.getElementById('btn-cancel-contact-edit');
            const btnSubmitContact = document.getElementById('btn-submit-contact');
            
            let currentProviderId = null;

            // Ao clicar no botão "Contatos" do fornecedor
            document.querySelectorAll('.btn-contacts-provider').forEach(button => {
                button.addEventListener('click', function() {
                    const providerId = this.getAttribute('data-id');
                    const providerName = this.getAttribute('data-name');
                    
                    currentProviderId = providerId;
                    providerNameSpan.textContent = providerName;
                    
                    resetContactForm();
                    loadContacts(providerId);
                    
                    contactsModal.show();
                });
            });

            // Resetar o formulário de contatos
            function resetContactForm() {
                contactForm.reset();
                contactIdInput.value = '';
                contactFormTitle.innerHTML = '<i class="fa-solid fa-user-plus me-1 text-success"></i> Novo Contato';
                btnCancelEdit.classList.add('d-none');
                btnSubmitContact.innerHTML = '<i class="fa-solid fa-save me-1"></i> Salvar Contato';
            }

            // Cancelar edição
            btnCancelEdit.addEventListener('click', resetContactForm);

            // Carregar contatos por AJAX
            function loadContacts(providerId) {
                contactsListContainer.innerHTML = '<tr><td colspan="5" class="text-center py-4"><i class="fa-solid fa-spinner fa-spin me-1 text-muted"></i> Carregando contatos...</td></tr>';
                
                fetch(`/providers/${providerId}/contacts`)
                    .then(response => response.json())
                    .then(contacts => {
                        renderContacts(contacts);
                    })
                    .catch(error => {
                        contactsListContainer.innerHTML = '<tr><td colspan="5" class="text-center text-danger py-4"><i class="fa-solid fa-triangle-exclamation me-1"></i> Erro ao carregar contatos.</td></tr>';
                        console.error(error);
                    });
            }

            // Renderizar lista de contatos
            function renderContacts(contacts) {
                if (contacts.length === 0) {
                    contactsListContainer.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-muted">Nenhum contato cadastrado para este fornecedor.</td></tr>';
                    return;
                }

                let html = '';
                contacts.forEach(contact => {
                    const isMainChecked = contact.is_main ? 'checked' : '';
                    const phone = contact.phone ? contact.phone : 'N/A';
                    const email = contact.email ? contact.email : 'N/A';
                    
                    html += `
                        <tr id="contact-row-${contact.id}">
                            <td><strong>${contact.name}</strong></td>
                            <td>${phone}</td>
                            <td>${email}</td>
                            <td class="text-center">
                                <input type="checkbox" class="form-check-input chk-toggle-main" data-id="${contact.id}" ${isMainChecked}>
                            </td>
                            <td class="text-end">
                                <button type="button" class="btn btn-xs btn-primary btn-edit-contact-ajax" data-contact='${JSON.stringify(contact)}' title="Editar Contato">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <button type="button" class="btn btn-xs btn-danger btn-delete-contact-ajax" data-id="${contact.id}" title="Excluir Contato">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                });
                
                contactsListContainer.innerHTML = html;
                bindContactEvents();
            }

            // Bind dos botões de ação na tabela de contatos
            function bindContactEvents() {
                // Alternar Contato Principal
                document.querySelectorAll('.chk-toggle-main').forEach(chk => {
                    chk.addEventListener('change', function() {
                        const id = this.getAttribute('data-id');
                        
                        fetch(`/provider-contacts/${id}/toggle-main`, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        })
                        .then(response => response.json())
                        .then(res => {
                            if (res.status === 'success') {
                                toastr.success(res.message);
                            } else {
                                toastr.error(res.message);
                            }
                            loadContacts(currentProviderId);
                        })
                        .catch(err => {
                            toastr.error('Erro ao processar solicitação.');
                            console.error(err);
                            loadContacts(currentProviderId);
                        });
                    });
                });

                // Editar Contato
                document.querySelectorAll('.btn-edit-contact-ajax').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const contact = JSON.parse(this.getAttribute('data-contact'));
                        
                        contactIdInput.value = contact.id;
                        contactNameInput.value = contact.name;
                        contactPhoneInput.value = contact.phone || '';
                        contactEmailInput.value = contact.email || '';
                        contactIsMainCheckbox.checked = contact.is_main;
                        
                        contactFormTitle.innerHTML = '<i class="fa-solid fa-user-pen me-1 text-primary"></i> Editar Contato';
                        btnCancelEdit.classList.remove('d-none');
                        btnSubmitContact.innerHTML = '<i class="fa-solid fa-save me-1"></i> Atualizar Contato';
                        
                        contactNameInput.focus();
                    });
                });

                // Excluir Contato
                document.querySelectorAll('.btn-delete-contact-ajax').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const id = this.getAttribute('data-id');
                        
                        Swal.fire({
                            title: 'Remover contato?',
                            text: "Esta ação não poderá ser desfeita.",
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#d33',
                            cancelButtonColor: '#3085d6',
                            confirmButtonText: 'Sim, excluir!',
                            cancelButtonText: 'Cancelar'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                fetch(`/provider-contacts/${id}`, {
                                    method: 'DELETE',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                    }
                                })
                                .then(response => response.json())
                                .then(res => {
                                    if (res.status === 'success') {
                                        toastr.success(res.message);
                                        loadContacts(currentProviderId);
                                    } else {
                                        toastr.error(res.message);
                                    }
                                })
                                .catch(err => {
                                    toastr.error('Erro ao processar remoção.');
                                    console.error(err);
                                });
                            }
                        });
                    });
                });
            }

            // Enviar Formulário de Contato (Novo ou Edição)
            contactForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const id = contactIdInput.value;
                const name = contactNameInput.value;
                const phone = contactPhoneInput.value;
                const email = contactEmailInput.value;
                const is_main = contactIsMainCheckbox.checked ? 1 : 0;
                
                const url = id ? `/provider-contacts/${id}` : `/providers/${currentProviderId}/contacts`;
                const method = id ? 'PUT' : 'POST';
                
                fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ name, phone, email, is_main })
                })
                .then(response => response.json())
                .then(res => {
                    if (res.status === 'success') {
                        toastr.success(res.message);
                        resetContactForm();
                        loadContacts(currentProviderId);
                    } else {
                        toastr.error(res.message);
                    }
                })
                .catch(err => {
                    toastr.error('Erro ao salvar contato.');
                    console.error(err);
                });
            });

            // View Mode Toggle
            const toggleTableBtn = document.getElementById('viewModeTableBtn');
            const toggleCardBtn = document.getElementById('viewModeCardBtn');
            const tableContainer = document.getElementById('view-table-container');
            const cardContainer = document.getElementById('view-card-container');
            
            if (toggleTableBtn && toggleCardBtn && tableContainer && cardContainer) {
                const savedMode = localStorage.getItem('view_mode_providers') || 'table';
                
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
                    localStorage.setItem('view_mode_providers', mode);
                };
                
                setMode(savedMode);
                
                toggleTableBtn.addEventListener('click', () => setMode('table'));
                toggleCardBtn.addEventListener('click', () => setMode('card'));
            }
        });
    </script>
@endpush
