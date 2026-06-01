@extends('layouts.app')

@section('page-title', 'Contratos de Serviços')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Contratos</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm mb-4">
                <div class="card-header border-0 bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center w-100">
                        <h5 class="mb-0 text-secondary">
                            <i class="fa-solid fa-file-contract me-2 text-primary"></i>
                            Lista de Contratos
                        </h5>
                        @if(!auth()->user()->isFornecedor())
                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#createContractModal">
                                <i class="fa-solid fa-plus me-1"></i> Novo Contrato
                            </button>
                        @endif
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($contracts->isEmpty())
                        <div class="text-center p-5">
                            <i class="fa-solid fa-file-invoice fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-0">Nenhum contrato cadastrado ou ativo.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Nº Contrato</th>
                                        <th>Objeto / Título</th>
                                        @if(auth()->user()->isSuperAdmin())
                                            <th>Empresa Contratante</th>
                                        @endif
                                        <th>Fornecedor</th>
                                        <th>Vigência (Início - Fim)</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-end px-4" style="width: 200px;">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($contracts as $contract)
                                        <tr>
                                            <td><strong>{{ $contract->contract_number }}</strong></td>
                                            <td>
                                                <div class="fw-bold">{{ $contract->title }}</div>
                                                <div class="text-muted fs-7">{{ Str::limit($contract->description, 50) }}</div>
                                                <span class="text-muted fs-8 d-block mt-1">
                                                    <i class="fa-solid fa-user me-1 text-primary"></i> Resp: {{ $contract->responsible->name ?? 'N/A' }}
                                                    <i class="fa-solid fa-bell ms-2 me-1 text-warning"></i> Alerta: {{ $contract->alert_days }} dias
                                                </span>
                                            </td>
                                            @if(auth()->user()->isSuperAdmin())
                                                <td>{{ $contract->company->name ?? 'N/A' }}</td>
                                            @endif
                                            <td>
                                                <div class="fw-bold">{{ $contract->provider->name ?? 'N/A' }}</div>
                                                <span class="text-muted fs-7">CNPJ: {{ $contract->provider->cnpj ?? 'N/A' }}</span>
                                            </td>
                                            <td>
                                                {{ $contract->start_date->format('d/m/Y') }} a {{ $contract->end_date->format('d/m/Y') }}
                                            </td>
                                            <td class="text-center">
                                                @switch($contract->status)
                                                    @case('active')
                                                        <span class="badge bg-success">Ativo</span>
                                                        @break
                                                    @case('expired')
                                                        <span class="badge bg-danger">Vencido</span>
                                                        @break
                                                    @case('suspended')
                                                        <span class="badge bg-warning text-dark">Suspenso</span>
                                                        @break
                                                    @case('draft')
                                                        <span class="badge bg-secondary">Rascunho</span>
                                                        @break
                                                @endswitch
                                            </td>
                                            <td class="text-end px-4">
                                                <div class="d-flex justify-content-end gap-2">
                                                    <a href="{{ route('contracts.show', $contract) }}" class="btn btn-sm btn-outline-info" title="Visualizar Linha do Tempo e Solicitações">
                                                        <i class="fa-solid fa-eye"></i> Detalhes
                                                    </a>
                                                    @if(!auth()->user()->isFornecedor())
                                                        <button type="button" class="btn btn-sm btn-primary btn-edit-contract"
                                                                data-id="{{ $contract->id }}"
                                                                data-company-id="{{ $contract->company_id }}"
                                                                data-provider-id="{{ $contract->provider_id }}"
                                                                data-responsible-id="{{ $contract->responsible_id }}"
                                                                data-contract-number="{{ $contract->contract_number }}"
                                                                data-title="{{ $contract->title }}"
                                                                data-description="{{ $contract->description }}"
                                                                data-start-date="{{ $contract->start_date->format('Y-m-d') }}"
                                                                data-end-date="{{ $contract->end_date->format('Y-m-d') }}"
                                                                data-alert-days="{{ $contract->alert_days }}"
                                                                data-status="{{ $contract->status }}"
                                                                data-url="{{ route('contracts.update', $contract) }}">
                                                            <i class="fa-solid fa-pen-to-square"></i> Editar
                                                        </button>
                                                    @endif
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

    @if(!auth()->user()->isFornecedor())
        <!-- MODAL DE CADASTRO (CREATE) -->
        <div class="modal fade" id="createContractModal" tabindex="-1" aria-labelledby="createContractModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <form action="{{ route('contracts.store') }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title" id="createContractModalLabel">Cadastrar Novo Contrato</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <!-- Empresa (Apenas se Super Admin) -->
                                @if(auth()->user()->isSuperAdmin())
                                    <div class="col-md-6 col-12 mb-3">
                                        <label for="create_company_id" class="form-label fw-bold">Empresa Contratante:</label>
                                        <select name="company_id" id="create_company_id" class="form-select @if($errors->any() && !old('_method')) is-invalid @endif" required>
                                            <option value="">Selecione a empresa...</option>
                                            @foreach($companies as $company)
                                                <option value="{{ $company->id }}" {{ old('company_id') == $company->id ? 'selected' : '' }}>
                                                    {{ $company->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif

                                <!-- Fornecedor -->
                                <div class="col-md-{{ auth()->user()->isSuperAdmin() ? '6' : '12' }} col-12 mb-3">
                                    <label for="create_provider_id" class="form-label fw-bold">Fornecedor Contratado:</label>
                                    <select name="provider_id" id="create_provider_id" class="form-select @if($errors->any() && !old('_method')) is-invalid @endif" required>
                                        <option value="">Selecione o fornecedor...</option>
                                        @foreach($providers as $provider)
                                            <option value="{{ $provider->id }}" {{ old('provider_id') == $provider->id ? 'selected' : '' }}>
                                                {{ $provider->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <!-- Número do Contrato -->
                                <div class="col-md-4 col-12 mb-3">
                                    <label for="create_contract_number" class="form-label fw-bold">Número do Contrato:</label>
                                    <input type="text" name="contract_number" id="create_contract_number" class="form-control @if($errors->any() && !old('_method')) is-invalid @endif" value="{{ old('contract_number') }}" placeholder="Ex: CTR-2026-99" required>
                                </div>

                                <!-- Título/Objeto do Contrato -->
                                <div class="col-md-8 col-12 mb-3">
                                    <label for="create_title" class="form-label fw-bold">Título / Objeto do Contrato:</label>
                                    <input type="text" name="title" id="create_title" class="form-control @if($errors->any() && !old('_method')) is-invalid @endif" value="{{ old('title') }}" placeholder="Ex: Serviços de apoio administrativo" required>
                                </div>
                            </div>

                            <!-- Descrição -->
                            <div class="mb-3">
                                <label for="create_description" class="form-label fw-bold">Descrição Detalhada (Opcional):</label>
                                <textarea name="description" id="create_description" class="form-control" rows="2" placeholder="Insira detalhes adicionais do contrato">{{ old('description') }}</textarea>
                            </div>

                            <div class="row">
                                <!-- Data de Início -->
                                <div class="col-md-6 col-12 mb-3">
                                    <label for="create_start_date" class="form-label fw-bold">Início da Vigência:</label>
                                    <input type="date" name="start_date" id="create_start_date" class="form-control @if($errors->any() && !old('_method')) is-invalid @endif" value="{{ old('start_date') }}" required>
                                </div>

                                <!-- Data de Término -->
                                <div class="col-md-6 col-12 mb-3">
                                    <label for="create_end_date" class="form-label fw-bold">Término da Vigência:</label>
                                    <input type="date" name="end_date" id="create_end_date" class="form-control @if($errors->any() && !old('_method')) is-invalid @endif" value="{{ old('end_date') }}" required>
                                </div>
                            </div>

                            <div class="row">
                                <!-- Responsável pelo Contrato -->
                                <div class="col-md-8 col-12 mb-3">
                                    <label for="create_responsible_id" class="form-label fw-bold">Responsável pelo Contrato:</label>
                                    <select name="responsible_id" id="create_responsible_id" class="form-select @if($errors->any() && !old('_method')) is-invalid @endif">
                                        <option value="">Selecione o responsável...</option>
                                        @foreach($responsibles as $resp)
                                            <option value="{{ $resp->id }}" {{ old('responsible_id') == $resp->id ? 'selected' : '' }}>
                                                {{ $resp->name }} ({{ $resp->role === 'super_admin' ? 'Admin' : 'Gestor' }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Dias para Alerta -->
                                <div class="col-md-4 col-12 mb-3">
                                    <label for="create_alert_days" class="form-label fw-bold">Alerta (Dias antes):</label>
                                    <input type="number" name="alert_days" id="create_alert_days" class="form-control @if($errors->any() && !old('_method')) is-invalid @endif" value="{{ old('alert_days', 30) }}" min="0" max="365" required>
                                </div>
                            </div>

                            <!-- Documentos Obrigatórios (Checklist de GED) -->
                            <div class="card bg-light border-0 mb-3 mt-2">
                                <div class="card-body py-3">
                                    <h6 class="fw-bold mb-2"><i class="fa-solid fa-folder-open text-primary me-2"></i> Obrigações Documentais (GED)</h6>
                                    <p class="text-muted fs-7 mb-3">Marque abaixo as obrigações documentais que serão exigidas para este contrato.</p>
                                    
                                    <div class="row">
                                        @foreach($documentTypes as $type)
                                            <div class="col-md-6 col-12 mb-2">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="document_types[]" value="{{ $type->id }}" id="doc_type_{{ $type->id }}" checked>
                                                    <label class="form-check-label" for="doc_type_{{ $type->id }}">
                                                        <strong>{{ $type->name }}</strong>
                                                        <span class="d-block text-muted fs-8">
                                                            Periodicidade: 
                                                            @switch($type->periodicity)
                                                                @case('monthly') Mensal @break
                                                                @case('quarterly') Trimestral @break
                                                                @case('semi-annual') Semestral @break
                                                                @case('annual') Anual @break
                                                                @case('once') Único @break
                                                            @endswitch
                                                        </span>
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
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
                            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save me-1"></i> Criar Contrato</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- MODAL DE EDIÇÃO (EDIT) -->
        <div class="modal fade" id="editContractModal" tabindex="-1" aria-labelledby="editContractModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <form id="form-edit-contract" action="" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- Campo escondido para rastrear o ID em caso de falha de validação -->
                        <input type="hidden" name="contract_id" id="edit_contract_id" value="{{ old('contract_id') }}">

                        <div class="modal-header">
                            <h5 class="modal-title" id="editContractModalLabel">Editar Dados do Contrato</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <!-- Empresa (Apenas se Super Admin) -->
                                @if(auth()->user()->isSuperAdmin())
                                    <div class="col-md-6 col-12 mb-3">
                                        <label for="edit_company_id" class="form-label fw-bold">Empresa Contratante:</label>
                                        <select name="company_id" id="edit_company_id" class="form-select @if($errors->any() && old('_method') === 'PUT') is-invalid @endif" required>
                                            @foreach($companies as $company)
                                                <option value="{{ $company->id }}" {{ old('company_id') == $company->id ? 'selected' : '' }}>
                                                    {{ $company->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif

                                <!-- Fornecedor -->
                                <div class="col-md-{{ auth()->user()->isSuperAdmin() ? '6' : '12' }} col-12 mb-3">
                                    <label for="edit_provider_id" class="form-label fw-bold">Fornecedor Contratado:</label>
                                    <select name="provider_id" id="edit_provider_id" class="form-select @if($errors->any() && old('_method') === 'PUT') is-invalid @endif" required>
                                        @foreach($providers as $provider)
                                            <option value="{{ $provider->id }}" {{ old('provider_id') == $provider->id ? 'selected' : '' }}>
                                                {{ $provider->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <!-- Número do Contrato -->
                                <div class="col-md-4 col-12 mb-3">
                                    <label for="edit_contract_number" class="form-label fw-bold">Número do Contrato:</label>
                                    <input type="text" name="contract_number" id="edit_contract_number" class="form-control @if($errors->any() && old('_method') === 'PUT') is-invalid @endif" value="{{ old('contract_number') }}" required>
                                </div>

                                <!-- Título/Objeto do Contrato -->
                                <div class="col-md-8 col-12 mb-3">
                                    <label for="edit_title" class="form-label fw-bold">Título / Objeto do Contrato:</label>
                                    <input type="text" name="title" id="edit_title" class="form-control @if($errors->any() && old('_method') === 'PUT') is-invalid @endif" value="{{ old('title') }}" required>
                                </div>
                            </div>

                            <!-- Descrição -->
                            <div class="mb-3">
                                <label for="edit_description" class="form-label fw-bold">Descrição Detalhada:</label>
                                <textarea name="description" id="edit_description" class="form-control" rows="2">{{ old('description') }}</textarea>
                            </div>

                            <div class="row">
                                <!-- Data de Início -->
                                <div class="col-md-4 col-12 mb-3">
                                    <label for="edit_start_date" class="form-label fw-bold">Início da Vigência:</label>
                                    <input type="date" name="start_date" id="edit_start_date" class="form-control @if($errors->any() && old('_method') === 'PUT') is-invalid @endif" value="{{ old('start_date') }}" required>
                                </div>

                                <!-- Data de Término -->
                                <div class="col-md-4 col-12 mb-3">
                                    <label for="edit_end_date" class="form-label fw-bold">Término da Vigência:</label>
                                    <input type="date" name="end_date" id="edit_end_date" class="form-control @if($errors->any() && old('_method') === 'PUT') is-invalid @endif" value="{{ old('end_date') }}" required>
                                </div>

                                <!-- Status -->
                                <div class="col-md-4 col-12 mb-3">
                                    <label for="edit_status" class="form-label fw-bold">Status:</label>
                                    <select name="status" id="edit_status" class="form-select @if($errors->any() && old('_method') === 'PUT') is-invalid @endif" required>
                                        <option value="active">Ativo</option>
                                        <option value="expired">Vencido</option>
                                        <option value="suspended">Suspenso</option>
                                        <option value="draft">Rascunho</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <!-- Responsável pelo Contrato -->
                                <div class="col-md-8 col-12 mb-3">
                                    <label for="edit_responsible_id" class="form-label fw-bold">Responsável pelo Contrato:</label>
                                    <select name="responsible_id" id="edit_responsible_id" class="form-select @if($errors->any() && old('_method') === 'PUT') is-invalid @endif">
                                        <option value="">Selecione o responsável...</option>
                                        @foreach($responsibles as $resp)
                                            <option value="{{ $resp->id }}">
                                                {{ $resp->name }} ({{ $resp->role === 'super_admin' ? 'Admin' : 'Gestor' }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Dias para Alerta -->
                                <div class="col-md-4 col-12 mb-3">
                                    <label for="edit_alert_days" class="form-label fw-bold">Alerta (Dias antes):</label>
                                    <input type="number" name="alert_days" id="edit_alert_days" class="form-control @if($errors->any() && old('_method') === 'PUT') is-invalid @endif" min="0" max="365" required>
                                </div>
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
    @endif
@endsection

@push('scripts')
    @if(!auth()->user()->isFornecedor())
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Inicializar Modais
                const createModal = new bootstrap.Modal(document.getElementById('createContractModal'));
                const editModal = new bootstrap.Modal(document.getElementById('editContractModal'));
                
                const editForm = document.getElementById('form-edit-contract');
                const editIdInput = document.getElementById('edit_contract_id');
                const editCompanyInput = document.getElementById('edit_company_id');
                const editProviderInput = document.getElementById('edit_provider_id');
                const editResponsibleInput = document.getElementById('edit_responsible_id');
                const editContractNumberInput = document.getElementById('edit_contract_number');
                const editTitleInput = document.getElementById('edit_title');
                const editDescriptionInput = document.getElementById('edit_description');
                const editStartDateInput = document.getElementById('edit_start_date');
                const editEndDateInput = document.getElementById('edit_end_date');
                const editAlertDaysInput = document.getElementById('edit_alert_days');
                const editStatusInput = document.getElementById('edit_status');

                // Capturar clique no botão editar
                document.querySelectorAll('.btn-edit-contract').forEach(button => {
                    button.addEventListener('click', function() {
                        const id = this.getAttribute('data-id');
                        const companyId = this.getAttribute('data-company-id');
                        const providerId = this.getAttribute('data-provider-id');
                        const responsibleId = this.getAttribute('data-responsible-id');
                        const contractNumber = this.getAttribute('data-contract-number');
                        const title = this.getAttribute('data-title');
                        const description = this.getAttribute('data-description');
                        const startDate = this.getAttribute('data-start-date');
                        const endDate = this.getAttribute('data-end-date');
                        const alertDays = this.getAttribute('data-alert-days');
                        const status = this.getAttribute('data-status');
                        const actionUrl = this.getAttribute('data-url');

                        // Preencher campos
                        editForm.setAttribute('action', actionUrl);
                        editIdInput.value = id;
                        if (editCompanyInput) editCompanyInput.value = companyId;
                        editProviderInput.value = providerId;
                        if (editResponsibleInput) editResponsibleInput.value = responsibleId !== 'null' ? responsibleId : '';
                        editContractNumberInput.value = contractNumber;
                        editTitleInput.value = title;
                        editDescriptionInput.value = description !== 'null' ? description : '';
                        editStartDateInput.value = startDate;
                        editEndDateInput.value = endDate;
                        if (editAlertDaysInput) editAlertDaysInput.value = alertDays;
                        editStatusInput.value = status;

                        // Mostrar modal
                        editModal.show();
                    });
                });

                // Recuperar modal aberto se houver erros de validação
                @if($errors->any())
                    @if(old('_method') === 'PUT')
                        const oldId = "{{ old('contract_id') }}";
                        if (oldId) {
                            editForm.setAttribute('action', "/contracts/" + oldId);
                            // Preencher os dados antigos da sessão
                            if (editCompanyInput) editCompanyInput.value = "{{ old('company_id') }}";
                            editProviderInput.value = "{{ old('provider_id') }}";
                            if (editResponsibleInput) editResponsibleInput.value = "{{ old('responsible_id') }}";
                            editContractNumberInput.value = "{{ old('contract_number') }}";
                            editTitleInput.value = "{{ old('title') }}";
                            editDescriptionInput.value = "{{ old('description') }}";
                            editStartDateInput.value = "{{ old('start_date') }}";
                            editEndDateInput.value = "{{ old('end_date') }}";
                            if (editAlertDaysInput) editAlertDaysInput.value = "{{ old('alert_days') }}";
                            editStatusInput.value = "{{ old('status') }}";
                            editModal.show();
                        }
                    @else
                        createModal.show();
                    @endif
                @endif
            });
        </script>
    @endif
@endpush
