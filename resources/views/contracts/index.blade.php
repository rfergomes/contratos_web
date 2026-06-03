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
                        <div class="d-flex align-items-center gap-3">
                            <!-- Filtro de Tipo de Gerenciamento -->
                            <div class="d-flex align-items-center gap-2">
                                <label for="filter-management-type" class="fs-7 text-secondary mb-0 d-none d-sm-inline">Gerenciamento:</label>
                                <select id="filter-management-type" class="form-select form-select-sm" style="width: 170px;">
                                    <option value="all">Todos</option>
                                    <option value="with_provider">Com Fornecedor</option>
                                    <option value="internal">Controle Interno</option>
                                </select>
                            </div>
                            <!-- Pesquisar -->
                            <div class="input-group input-group-sm" style="width: 14rem">
                                <input type="search" class="form-control" id="searchFilter" placeholder="Pesquisar..." aria-label="Pesquisar">
                                <span class="input-group-text">
                                    <i class="bi bi-search" aria-hidden="true"></i>
                                </span>
                            </div>
                            
                            <!-- Alternador Tabela / Cards (AdminLTE btn-group) -->
                            <div class="btn-group btn-group-sm" role="group" aria-label="Modo de visualização">
                                <input type="radio" class="btn-check" name="viewMode" id="viewModeCardBtn" autocomplete="off">
                                <label class="btn btn-outline-secondary" for="viewModeCardBtn" title="Visualização em Cards">
                                    <i class="bi bi-grid-3x3-gap" aria-hidden="true"></i>
                                </label>
                                <input type="radio" class="btn-check" name="viewMode" id="viewModeTableBtn" autocomplete="off">
                                <label class="btn btn-outline-secondary" for="viewModeTableBtn" title="Visualização em Tabela">
                                    <i class="bi bi-list-ul" aria-hidden="true"></i>
                                </label>
                            </div>
                            @if(!auth()->user()->isFornecedor())
                                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#createContractModal">
                                    <i class="fa-solid fa-plus me-1"></i> Novo
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($contracts->isEmpty())
                        <div class="text-center p-5">
                            <i class="fa-solid fa-file-invoice fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-0">Nenhum contrato cadastrado ou ativo.</p>
                        </div>
                    @else
                        <!-- MODO TABELA -->
                        <div id="view-table-container" class="table-responsive">
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
                                        <tr class="contract-row" data-management-type="{{ $contract->isInternal() ? 'internal' : 'with_provider' }}">
                                            <td>
                                                <strong>{{ $contract->contract_number }}</strong>
                                                @if($contract->isInternal())
                                                    <span class="badge bg-secondary d-block mt-1 fs-8">Interno</span>
                                                @else
                                                    <span class="badge bg-primary d-block mt-1 fs-8">Externo</span>
                                                @endif
                                            </td>
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
                                                @if($contract->isExternal() && $contract->provider)
                                                    <div class="fw-bold">{{ $contract->provider->name }}</div>
                                                    <span class="text-muted fs-7">CNPJ: {{ $contract->provider->cnpj }}</span>
                                                @else
                                                    <div class="fw-bold text-secondary">Controle Interno</div>
                                                    <span class="text-muted fs-8">Uso Interno</span>
                                                @endif
                                            </td>
                                            <td>
                                                {{ $contract->start_date->format('d/m/Y') }} a {{ $contract->end_date->format('d/m/Y') }}
                                            </td>
                                            <td class="text-center">
                                                @switch($contract->status)
                                                    @case('pending')
                                                        <span class="badge bg-info">Pendente</span>
                                                        @break
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
                                                                data-management-type="{{ $contract->management_type }}"
                                                                data-url="{{ route('contracts.update', $contract) }}">
                                                            <i class="fa-solid fa-pen-to-square"></i> Editar
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                    <tr id="table-empty-row" style="display: none;">
                                        <td colspan="@if(auth()->user()->isSuperAdmin()) 7 @else 6 @endif" class="text-center py-4 text-muted">
                                            <i class="fa-solid fa-circle-exclamation me-1"></i> Nenhum contrato corresponde ao filtro selecionado.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- MODO CARDS -->
                        <div id="view-card-container" class="p-4 d-none">
                            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-3">
                                @foreach($contracts as $contract)
                                    <div class="col card-item" data-management-type="{{ $contract->isInternal() ? 'internal' : 'with_provider' }}">
                                        <div class="card h-100 card-secondary card-outline shadow-sm">

                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-start mb-3">
                                                    <div class="d-flex align-items-center">
                                                        <div class="bg-primary-subtle text-primary rounded p-2 me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                            <i class="fa-solid fa-file-contract fs-5"></i>
                                                        </div>
                                                        <div>
                                                            <h6 class="fw-bold mb-0 text-dark">{{ $contract->title }}</h6>
                                                            <small class="text-muted">Nº: {{ $contract->contract_number }}</small>
                                                        </div>
                                                    </div>
                                                    <div class="text-end">
                                                        @switch($contract->status)
                                                            @case('pending')
                                                                <span class="badge bg-info">Pendente</span>
                                                                @break
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
                                                        @if($contract->isInternal())
                                                            <span class="badge bg-secondary d-block mt-1 fs-8">Interno</span>
                                                        @else
                                                            <span class="badge bg-primary d-block mt-1 fs-8">Externo</span>
                                                        @endif
                                                    </div>
                                                </div>
                                                
                                                @if(auth()->user()->isSuperAdmin())
                                                    <p class="mb-1 fs-8 text-secondary">
                                                        <strong>Contratante:</strong> {{ $contract->company->name ?? 'N/A' }}
                                                    </p>
                                                @endif
                                                <p class="mb-2 fs-7 text-secondary">
                                                    <strong>Fornecedor:</strong> {{ $contract->isExternal() ? ($contract->provider->name ?? 'N/A') : 'Controle Interno' }}
                                                </p>
                                                <p class="mb-2 fs-8 text-muted">
                                                    {{ Str::limit($contract->description, 100) }}
                                                </p>
                                                <p class="mb-2 fs-8 text-secondary">
                                                    <strong>Vigência:</strong> {{ $contract->start_date->format('d/m/Y') }} a {{ $contract->end_date->format('d/m/Y') }}
                                                </p>
                                                <div class="mb-3 fs-8 text-muted border-top pt-2 mt-2">
                                                    <span class="d-block"><i class="fa-solid fa-user me-1 text-primary"></i> Resp: {{ $contract->responsible->name ?? 'N/A' }}</span>
                                                    <span class="d-block"><i class="fa-solid fa-bell me-1 text-warning"></i> Alerta de renovação: {{ $contract->alert_days }} dias</span>
                                                </div>
                                                
                                                <div class="d-flex justify-content-end gap-2 border-top pt-3">
                                                    <a href="{{ route('contracts.show', $contract) }}" class="btn btn-xs btn-outline-info">
                                                        <i class="fa-solid fa-eye me-1"></i> Detalhes
                                                    </a>
                                                    @if(!auth()->user()->isFornecedor())
                                                        <button type="button" class="btn btn-xs btn-primary btn-edit-contract"
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
                                                                data-management-type="{{ $contract->management_type }}"
                                                                data-url="{{ route('contracts.update', $contract) }}">
                                                            <i class="fa-solid fa-pen-to-square me-1"></i> Editar
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                                <div id="card-empty-div" class="col-12 text-center py-4 text-muted d-none">
                                    <i class="fa-solid fa-circle-exclamation me-1"></i> Nenhum contrato corresponde ao filtro selecionado.
                                </div>
                            </div>
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
                                <!-- Tipo de Gerenciamento -->
                                <div class="col-md-6 col-12 mb-3">
                                    <label for="create_management_type" class="form-label fw-bold">Tipo de Gerenciamento:</label>
                                    <select name="management_type" id="create_management_type" class="form-select" required>
                                        <option value="external" {{ old('management_type') == 'external' ? 'selected' : '' }}>Fornecedor Externo</option>
                                        <option value="internal" {{ old('management_type', 'external') == 'internal' ? 'selected' : '' }}>Controle Interno</option>
                                    </select>
                                </div>

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
                            </div>

                            <div class="row" id="create_provider_wrapper">
                                <!-- Fornecedor -->
                                <div class="col-12 mb-3">
                                    <label for="create_provider_id" class="form-label fw-bold">Fornecedor Contratado:</label>
                                    <select name="provider_id" id="create_provider_id" class="form-select @if($errors->any() && !old('_method')) is-invalid @endif">
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
                                <!-- Tipo de Gerenciamento -->
                                <div class="col-md-6 col-12 mb-3">
                                    <label for="edit_management_type" class="form-label fw-bold">Tipo de Gerenciamento:</label>
                                    <select name="management_type" id="edit_management_type" class="form-select" required>
                                        <option value="external">Fornecedor Externo</option>
                                        <option value="internal">Controle Interno</option>
                                    </select>
                                </div>

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
                            </div>

                            <div class="row" id="edit_provider_wrapper">
                                <!-- Fornecedor -->
                                <div class="col-12 mb-3">
                                    <label for="edit_provider_id" class="form-label fw-bold">Fornecedor Contratado:</label>
                                    <select name="provider_id" id="edit_provider_id" class="form-select @if($errors->any() && old('_method') === 'PUT') is-invalid @endif">
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
                                        <option value="pending">Pendente</option>
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
                const editManagementTypeInput = document.getElementById('edit_management_type');

                // Lógica de alternar exibição de Fornecedor
                const createManagementTypeSelect = document.getElementById('create_management_type');
                const createProviderWrapper = document.getElementById('create_provider_wrapper');
                
                const toggleCreateProvider = (type) => {
                    if (type === 'internal') {
                        createProviderWrapper.style.display = 'none';
                        document.getElementById('create_provider_id').value = '';
                    } else {
                        createProviderWrapper.style.display = 'block';
                    }
                };
                
                if (createManagementTypeSelect) {
                    createManagementTypeSelect.addEventListener('change', function() {
                        toggleCreateProvider(this.value);
                    });
                    toggleCreateProvider(createManagementTypeSelect.value);
                }

                const editManagementTypeSelect = document.getElementById('edit_management_type');
                const editProviderWrapper = document.getElementById('edit_provider_wrapper');
                
                const toggleEditProvider = (type) => {
                    if (type === 'internal') {
                        editProviderWrapper.style.display = 'none';
                        document.getElementById('edit_provider_id').value = '';
                    } else {
                        editProviderWrapper.style.display = 'block';
                    }
                };
                
                if (editManagementTypeSelect) {
                    editManagementTypeSelect.addEventListener('change', function() {
                        toggleEditProvider(this.value);
                    });
                }

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
                        const managementType = this.getAttribute('data-management-type') || 'external';
                        const actionUrl = this.getAttribute('data-url');

                        // Preencher campos
                        editForm.setAttribute('action', actionUrl);
                        editIdInput.value = id;
                        if (editCompanyInput) editCompanyInput.value = companyId;
                        editProviderInput.value = (providerId && providerId !== 'null') ? providerId : '';
                        if (editResponsibleInput) editResponsibleInput.value = responsibleId !== 'null' ? responsibleId : '';
                        editContractNumberInput.value = contractNumber;
                        editTitleInput.value = title;
                        editDescriptionInput.value = description !== 'null' ? description : '';
                        editStartDateInput.value = startDate;
                        editEndDateInput.value = endDate;
                        if (editAlertDaysInput) editAlertDaysInput.value = alertDays;
                        editStatusInput.value = status;
                        if (editManagementTypeInput) {
                            editManagementTypeInput.value = managementType;
                            toggleEditProvider(managementType);
                        }

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
                            if (editManagementTypeInput) {
                                const oldMgmtType = "{{ old('management_type') }}";
                                editManagementTypeInput.value = oldMgmtType;
                                toggleEditProvider(oldMgmtType);
                            }
                            editModal.show();
                        }
                    @else
                        if (createManagementTypeSelect) {
                            toggleCreateProvider(createManagementTypeSelect.value);
                        }
                        createModal.show();
                    @endif
                @endif
            });
        </script>
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggleTableRadio = document.getElementById('viewModeTableBtn');
            const toggleCardRadio = document.getElementById('viewModeCardBtn');
            const tableContainer = document.getElementById('view-table-container');
            const cardContainer = document.getElementById('view-card-container');
            
            if (toggleTableRadio && toggleCardRadio && tableContainer && cardContainer) {
                const savedMode = localStorage.getItem('view_mode_contracts') || 'table';
                
                const setMode = (mode) => {
                    if (mode === 'card') {
                        tableContainer.classList.add('d-none');
                        cardContainer.classList.remove('d-none');
                        toggleCardRadio.checked = true;
                    } else {
                        tableContainer.classList.remove('d-none');
                        cardContainer.classList.add('d-none');
                        toggleTableRadio.checked = true;
                    }
                    localStorage.setItem('view_mode_contracts', mode);
                };
                
                setMode(savedMode);
                
                toggleTableRadio.addEventListener('change', () => setMode('table'));
                toggleCardRadio.addEventListener('change', () => setMode('card'));
            }

            // Client-side filtering logic (dropdown and search text)
            const filterSelect = document.getElementById('filter-management-type');
            const searchInput = document.getElementById('searchFilter');

            function applyFilters() {
                const filterValue = filterSelect ? filterSelect.value : 'all';
                const searchQuery = searchInput ? searchInput.value.toLowerCase().trim() : '';

                // Filter table rows
                const tableRows = document.querySelectorAll('.contract-row');
                let visibleTableCount = 0;
                tableRows.forEach(row => {
                    const managementType = row.getAttribute('data-management-type');
                    const textMatches = row.textContent.toLowerCase().includes(searchQuery);
                    const typeMatches = filterValue === 'all' || managementType === filterValue;

                    if (typeMatches && textMatches) {
                        row.style.display = '';
                        visibleTableCount++;
                    } else {
                        row.style.display = 'none';
                    }
                });

                const tableEmptyRow = document.getElementById('table-empty-row');
                if (tableEmptyRow) {
                    tableEmptyRow.style.display = visibleTableCount === 0 ? '' : 'none';
                }

                // Filter card items
                const cardItems = document.querySelectorAll('.card-item');
                let visibleCardCount = 0;
                cardItems.forEach(card => {
                    const managementType = card.getAttribute('data-management-type');
                    const textMatches = card.textContent.toLowerCase().includes(searchQuery);
                    const typeMatches = filterValue === 'all' || managementType === filterValue;

                    if (typeMatches && textMatches) {
                        card.classList.remove('d-none');
                        visibleCardCount++;
                    } else {
                        card.classList.add('d-none');
                    }
                });

                const cardEmptyDiv = document.getElementById('card-empty-div');
                if (cardEmptyDiv) {
                    if (visibleCardCount === 0) {
                        cardEmptyDiv.classList.remove('d-none');
                    } else {
                        cardEmptyDiv.classList.add('d-none');
                    }
                }
            }

            if (filterSelect) {
                filterSelect.addEventListener('change', applyFilters);
            }
            if (searchInput) {
                searchInput.addEventListener('input', applyFilters);
            }
        });
    </script>
@endpush
