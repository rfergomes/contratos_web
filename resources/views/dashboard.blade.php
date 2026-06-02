@extends('layouts.app')

@section('page-title', 'Dashboard')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
@endsection

@section('content')
    <!-- Saudação Inicial -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-light text-dark shadow-md">
                <div class="card-body p-4">
                    <h5 class="mb-2">Olá, {{ auth()->user()->name }}!</h5>
                    <p class="mb-0 fs-6 opacity-75">
                        @if(auth()->user()->isSuperAdmin())
                            Você está logado como <strong>Administrador Global</strong>. Aqui está a visão geral do sistema.
                        @elseif(auth()->user()->isGestor())
                            Você está logado como gestor de contratos da empresa <strong>{{ auth()->user()->company->name ?? 'N/A' }}</strong>.
                        @else
                            Bem-vindo ao portal de fornecedores da empresa <strong>{{ auth()->user()->provider->name ?? 'N/A' }}</strong>. Acompanhe abaixo as suas obrigações documentais.
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Widgets Estatísticos Dinâmicos -->
    <div class="row">
        @if(auth()->user()->isSuperAdmin())
            <!-- Widgets para Super Admin -->
            <div class="col-lg-3 col-6">
                <div class="small-box text-bg-primary">
                    <div class="inner">
                        <h3>{{ $stats['companies'] }}</h3>
                        <p>Empresas</p>
                    </div>
                    <svg class="small-box-icon" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M3.75 3a.75.75 0 00-.75.75v.75h16.5v-.75A.75.75 0 0018.75 3H3.75zM3 7.5v11.25c0 .414.336.75.75.75h14.5A.75.75 0 0019 18.75V7.5H3zm5.25 3a.75.75 0 01.75-.75h4.5a.75.75 0 010 1.5H9a.75.75 0 01-.75-.75zm0 3a.75.75 0 01.75-.75h4.5a.75.75 0 010 1.5H9a.75.75 0 01-.75-.75zM6.75 16.5a.75.75 0 100-1.5.75.75 0 000 1.5zm.75-4.5a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm6 4.5a.75.75 0 100-1.5.75.75 0 000 1.5zm.75-4.5a.75.75 0 11-1.5 0 .75.75 0 011.5 0z"/>
                    </svg>
                    <a href="{{ route('companies.index') }}" class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover">
                        Ver Empresas <i class="bi bi-arrow-right-short"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box text-bg-success">
                    <div class="inner">
                        <h3>{{ $stats['providers'] }}</h3>
                        <p>Fornecedores Ativos</p>
                    </div>
                    <svg class="small-box-icon" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M4.5 6.375a4.125 4.125 0 118.25 0 4.125 4.125 0 01-8.25 0zM14.25 8.625a3.375 3.375 0 116.75 0 3.375 3.375 0 01-6.75 0zM1.5 19.125a7.125 7.125 0 0114.25 0v.003l-.001.119a.75.75 0 01-.363.63 13.067 13.067 0 01-6.761 1.873c-2.472 0-4.786-.684-6.76-1.873a.75.75 0 01-.364-.63l-.001-.122zM17.25 19.128l-.001.144a2.25 2.25 0 01-.233.96 10.088 10.088 0 005.06-1.01.75.75 0 00.42-.643 4.875 4.875 0 00-6.957-4.611 8.586 8.586 0 011.71 5.157v.003z"/>
                    </svg>
                    <a href="{{ route('providers.index') }}" class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover">
                        Ver Fornecedores <i class="bi bi-arrow-right-short"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box text-bg-warning">
                    <div class="inner">
                        <h3>{{ $stats['contracts'] }}</h3>
                        <p>Contratos Cadastrados</p>
                    </div>
                    <svg class="small-box-icon" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path fill-rule="evenodd" d="M5.625 1.5c-1.036 0-1.875.84-1.875 1.875v17.25c0 1.035.84 1.875 1.875 1.875h12.75c1.035 0 1.875-.84 1.875-1.875V12.75A3.75 3.75 0 0016.5 9h-1.875a1.875 1.875 0 01-1.875-1.875V5.25A3.75 3.75 0 009 1.5H5.625zM7.5 15a.75.75 0 01.75-.75h7.5a.75.75 0 010 1.5h-7.5A.75.75 0 017.5 15zm.75 2.25a.75.75 0 000 1.5H12a.75.75 0 000-1.5H8.25z" clip-rule="evenodd"/>
                        <path d="M12.971 1.816A5.23 5.23 0 0114.25 5.25v1.875c0 .207.168.375.375.375H16.5a5.23 5.23 0 013.434 1.279 9.768 9.768 0 00-6.963-6.963z"/>
                    </svg>
                    <a href="{{ route('contracts.index') }}" class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover">
                        Ver Contratos <i class="bi bi-arrow-right-short"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box text-bg-danger">
                    <div class="inner">
                        <h3>{{ $stats['documents'] }}</h3>
                        <p>Documentos Exigidos</p>
                    </div>
                    <svg class="small-box-icon" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12zm13.36-1.814a.75.75 0 10-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 00-1.06 1.06l2.25 2.25a.75.75 0 001.14-.094l3.75-5.25z" clip-rule="evenodd"/>
                    </svg>
                    <a href="{{ route('contracts.index') }}" class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover">
                        Ver Detalhes <i class="bi bi-arrow-right-short"></i>
                    </a>
                </div>
            </div>
        @elseif(auth()->user()->isGestor())
            <!-- Widgets para Gestor da Empresa -->
            <div class="col-lg-3 col-6">
                <div class="small-box text-bg-info">
                    <div class="inner">
                        <h3>{{ $stats['active_contracts'] }}</h3>
                        <p>Contratos Ativos</p>
                    </div>
                    <svg class="small-box-icon" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path fill-rule="evenodd" d="M5.625 1.5c-1.036 0-1.875.84-1.875 1.875v17.25c0 1.035.84 1.875 1.875 1.875h12.75c1.035 0 1.875-.84 1.875-1.875V12.75A3.75 3.75 0 0016.5 9h-1.875a1.875 1.875 0 01-1.875-1.875V5.25A3.75 3.75 0 009 1.5H5.625zM7.5 15a.75.75 0 01.75-.75h7.5a.75.75 0 010 1.5h-7.5A.75.75 0 017.5 15zm.75 2.25a.75.75 0 000 1.5H12a.75.75 0 000-1.5H8.25z" clip-rule="evenodd"/>
                        <path d="M12.971 1.816A5.23 5.23 0 0114.25 5.25v1.875c0 .207.168.375.375.375H16.5a5.23 5.23 0 013.434 1.279 9.768 9.768 0 00-6.963-6.963z"/>
                    </svg>
                    <a href="{{ route('contracts.index') }}" class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover">
                        Ver Contratos <i class="bi bi-arrow-right-short"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box text-bg-warning">
                    <div class="inner">
                        <h3>{{ $stats['submitted_documents'] }}</h3>
                        <p>Documentos em Análise</p>
                    </div>
                    <svg class="small-box-icon" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25zM12.75 6a.75.75 0 00-1.5 0v6c0 .414.336.75.75.75h4.5a.75.75 0 000-1.5h-3.75V6z" clip-rule="evenodd"/>
                    </svg>
                    <a href="{{ route('contracts.index') }}" class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover">
                        Ver Pendências <i class="bi bi-arrow-right-short"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box text-bg-danger">
                    <div class="inner">
                        <h3>{{ $stats['pending_documents'] }}</h3>
                        <p>Documentos Pendentes</p>
                    </div>
                    <svg class="small-box-icon" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path fill-rule="evenodd" d="M9.401 3.003c1.155-2 4.043-2 5.197 0l7.355 12.748c1.154 2-.29 4.5-2.599 4.5H4.645c-2.309 0-3.752-2.5-2.598-4.5L9.4 3.003zM12 8.25a.75.75 0 01.75.75v3.75a.75.75 0 01-1.5 0V9a.75.75 0 01.75-.75zm0 8.25a.75.75 0 100-1.5.75.75 0 000 1.5z" clip-rule="evenodd"/>
                    </svg>
                    <a href="{{ route('contracts.index') }}" class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover">
                        Ver Pendentes <i class="bi bi-arrow-right-short"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box text-bg-success">
                    <div class="inner">
                        <h3>{{ $stats['approved_documents'] }}</h3>
                        <p>Documentos Aprovados</p>
                    </div>
                    <svg class="small-box-icon" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12zm13.36-1.814a.75.75 0 10-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 00-1.06 1.06l2.25 2.25a.75.75 0 001.14-.094l3.75-5.25z" clip-rule="evenodd"/>
                    </svg>
                    <a href="{{ route('contracts.index') }}" class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover">
                        Ver Aprovados <i class="bi bi-arrow-right-short"></i>
                    </a>
                </div>
            </div>
        @else
            <!-- Widgets para Fornecedor -->
            <div class="col-lg-4 col-12">
                <div class="small-box text-bg-danger">
                    <div class="inner">
                        <h3>{{ $stats['pending_obligations'] }}</h3>
                        <p>Obrigações Pendentes</p>
                    </div>
                    <svg class="small-box-icon" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path fill-rule="evenodd" d="M9.401 3.003c1.155-2 4.043-2 5.197 0l7.355 12.748c1.154 2-.29 4.5-2.599 4.5H4.645c-2.309 0-3.752-2.5-2.598-4.5L9.4 3.003zM12 8.25a.75.75 0 01.75.75v3.75a.75.75 0 01-1.5 0V9a.75.75 0 01.75-.75zm0 8.25a.75.75 0 100-1.5.75.75 0 000 1.5z" clip-rule="evenodd"/>
                    </svg>
                    <a href="{{ route('contracts.index') }}" class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover">
                        Ver Obrigações <i class="bi bi-arrow-right-short"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-4 col-6">
                <div class="small-box text-bg-warning">
                    <div class="inner">
                        <h3>{{ $stats['submitted_documents'] }}</h3>
                        <p>Enviados em Análise</p>
                    </div>
                    <svg class="small-box-icon" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M3.478 2.405a.75.75 0 00-.926.94l2.432 7.905H13.5a.75.75 0 010 1.5H4.984l-2.432 7.905a.75.75 0 00.926.94 60.519 60.519 0 0018.445-8.986.75.75 0 000-1.218A60.517 60.517 0 003.478 2.405z"/>
                    </svg>
                    <a href="{{ route('contracts.index') }}" class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover">
                        Ver Enviados <i class="bi bi-arrow-right-short"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-4 col-6">
                <div class="small-box text-bg-success">
                    <div class="inner">
                        <h3>{{ $stats['compliant_documents'] }}</h3>
                        <p>Documentos em Conformidade</p>
                    </div>
                    <svg class="small-box-icon" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path fill-rule="evenodd" d="M12.516 2.17a.75.75 0 00-1.032 0 11.209 11.209 0 01-7.877 3.08.75.75 0 00-.722.515A12.74 12.74 0 002.25 9.75c0 5.942 4.064 10.933 9.563 12.348a.749.749 0 00.374 0c5.499-1.415 9.563-6.406 9.563-12.348 0-1.39-.223-2.73-.635-3.985a.75.75 0 00-.722-.516l-.143.001c-2.996 0-5.717-1.17-7.734-3.08zm3.094 8.016a.75.75 0 10-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 00-1.06 1.06l2.25 2.25a.75.75 0 001.14-.094l3.75-5.25z" clip-rule="evenodd"/>
                    </svg>
                    <a href="{{ route('contracts.index') }}" class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover">
                        Ver Conformes <i class="bi bi-arrow-right-short"></i>
                    </a>
                </div>
            </div>
        @endif
    </div>

    <!-- Visão Geral de Contratos / Pendências -->
    <div class="row">
        <!-- Contratos Recentes (Cards responsivos) -->
        <div class="col-lg-8 col-12 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header border-0 bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center w-100">
                        <h5 class="mb-0 text-secondary">
                            <i class="fa-solid fa-file-invoice-dollar me-2 text-primary"></i>
                            {{ auth()->user()->isFornecedor() ? 'Meus Contratos Ativos' : 'Últimos Contratos Registrados' }}
                        </h5>
                        <a href="{{ route('contracts.index') }}" class="btn btn-sm btn-outline-primary">Ver Todos</a>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($recentContracts->isEmpty())
                        <div class="text-center p-5">
                            <i class="fa-solid fa-file-signature fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-0">Nenhum contrato ativo ou registrado.</p>
                        </div>
                    @else
                        {{-- Cards responsivos: funcionam bem em mobile e desktop --}}
                        @foreach($recentContracts as $contract)
                            @php
                                $statusColor = match($contract->status) {
                                    'active'    => '#28a745',
                                    'pending'   => '#0dcaf0',
                                    'expired'   => '#dc3545',
                                    'suspended' => '#ffc107',
                                    default     => '#6c757d',
                                };
                            @endphp
                            <a href="{{ route('contracts.show', $contract) }}" class="d-block text-decoration-none border-bottom dashboard-contract-row" style="border-left: 4px solid {{ $statusColor }} !important;">
                                <div class="px-3 py-2">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="min-w-0 flex-grow-1 me-2">
                                            <div class="d-flex align-items-center flex-wrap gap-2 mb-1">
                                                <span class="fw-bold text-dark fs-7">{{ $contract->contract_number }}</span>
                                                @switch($contract->status)
                                                    @case('pending')  <span class="badge bg-info">Pendente</span> @break
                                                    @case('active')   <span class="badge bg-success">Ativo</span> @break
                                                    @case('expired')  <span class="badge bg-danger">Vencido</span> @break
                                                    @case('suspended')<span class="badge bg-warning text-dark">Suspenso</span> @break
                                                    @case('draft')    <span class="badge bg-secondary">Rascunho</span> @break
                                                @endswitch
                                            </div>
                                            <div class="fw-semibold text-dark fs-7">{{ $contract->title }}</div>
                                            <div class="text-muted fs-8">{{ Str::limit($contract->description, 60) }}</div>
                                            <div class="text-muted fs-8 mt-1">
                                                @if(!auth()->user()->isFornecedor())
                                                    <i class="fa-solid fa-user me-1"></i>
                                                    <span>Resp: {{ $contract->responsible->name ?? 'N/A' }}</span>
                                                @endif
                                                <i class="fa-solid fa-bell ms-2 me-1 text-warning"></i>
                                                <span>Alerta: {{ $contract->alert_days }} dias</span>
                                            </div>
                                        </div>
                                        <div class="text-end flex-shrink-0">
                                            @if(!auth()->user()->isFornecedor())
                                                <div class="fw-semibold text-dark fs-8">{{ Str::limit($contract->provider->name ?? 'N/A', 15) }}</div>
                                                <div class="text-muted fs-8">{{ $contract->provider->cnpj ?? '' }}</div>
                                            @endif
                                            <div class="text-muted fs-8 mt-1">
                                                {{ $contract->start_date->format('d/m/y') }}<br>
                                                <span class="text-muted">a {{ $contract->end_date->format('d/m/y') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>

        <!-- Painel de Gerenciamento de Alertas -->
        <div class="col-lg-4 col-12 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header border-0 bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-secondary">
                        <i class="fa-solid fa-bell me-2 text-warning"></i>
                        Alertas e Notificações
                    </h5>
                    @if($unreadAlerts->isNotEmpty())
                        <button type="button" id="btn-read-all-alerts" class="btn btn-xs btn-outline-secondary">
                            Ler Todos
                        </button>
                    @endif
                </div>
                <div class="card-body p-0">
                    <div id="alerts-list-container" class="list-group list-group-flush" style="max-height: 450px; overflow-y: auto;">
                        @if($unreadAlerts->isEmpty())
                            <div class="text-center p-5 text-muted" id="empty-alerts-state">
                                <i class="fa-solid fa-circle-check text-success fa-3x mb-3"></i>
                                <p class="mb-0 fw-bold">Tudo em ordem!</p>
                                <p class="text-muted fs-8 mb-0">Você não possui alertas ou prazos pendentes.</p>
                            </div>
                        @else
                            @foreach($unreadAlerts as $alert)
                                @php
                                    $alertIcons = [
                                        'new_request' => 'fa-envelope text-primary bg-primary-subtle',
                                        'request_deadline' => 'fa-clock text-warning bg-warning-subtle',
                                        'obligation_deadline' => 'fa-circle-exclamation text-danger bg-danger-subtle',
                                        'request_response' => 'fa-reply text-success bg-success-subtle',
                                    ];
                                    $alertIcon = $alertIcons[$alert->type] ?? 'fa-bell text-secondary bg-light';
                                @endphp
                                <div class="list-group-item d-flex align-items-start p-3 alert-item-row" id="alert-row-{{ $alert->id }}">
                                    <!-- Icone -->
                                    <div class="flex-shrink-0 rounded-circle p-2 d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                        <i class="fa-solid {{ $alertIcon }} fs-5"></i>
                                    </div>
                                    <!-- Conteudo -->
                                    <div class="flex-grow-1 min-width-0">
                                        <div class="d-flex justify-content-between align-items-baseline mb-1">
                                            <span class="fw-bold fs-7 text-dark text-truncate" style="max-width: 170px;">{{ $alert->title }}</span>
                                            <span class="text-muted fs-8">{{ $alert->created_at->diffForHumans() }}</span>
                                        </div>
                                        <p class="mb-2 text-secondary fs-8 text-wrap" style="line-height: 1.3;">{{ $alert->message }}</p>
                                        
                                        <!-- Ações do Alerta -->
                                        <div class="d-flex gap-2">
                                            <a href="{{ route('alerts.go', $alert) }}" class="btn btn-xs btn-primary d-flex align-items-center gap-1">
                                                <i class="fa-solid fa-arrow-up-right-from-square"></i> Navegar
                                            </a>
                                            <button type="button" class="btn btn-xs btn-outline-secondary btn-read-alert" data-id="{{ $alert->id }}">
                                                <i class="fa-solid fa-check"></i> Lido
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Seletor dos botões de leitura de alertas individuais
            document.querySelectorAll('.btn-read-alert').forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const alertId = this.getAttribute('data-id');
                    const alertRow = document.getElementById(`alert-row-${alertId}`);

                    fetch(`/alerts/${alertId}/read`, {
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
                            
                            // Efeito suave de fadeOut e remoção
                            alertRow.style.transition = 'all 0.4s ease';
                            alertRow.style.opacity = '0';
                            alertRow.style.height = '0';
                            alertRow.style.padding = '0';
                            
                            setTimeout(() => {
                                alertRow.remove();
                                checkEmptyAlertState();
                            }, 400);
                        } else {
                            toastr.error('Falha ao marcar alerta como lido.');
                        }
                    })
                    .catch(err => {
                        toastr.error('Erro de conexão ao processar leitura.');
                        console.error(err);
                    });
                });
            });

            // Seletor para ler todos os alertas
            const readAllBtn = document.getElementById('btn-read-all-alerts');
            if (readAllBtn) {
                readAllBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    fetch('/alerts/read-all', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(response => response.json())
                    .then(res => {
                        if (res.status === 'success') {
                            toastr.success(res.message);
                            
                            // Remove o botão de ler tudo
                            readAllBtn.remove();
                            
                            // FadeOut de todas as linhas de alerta
                            document.querySelectorAll('.alert-item-row').forEach(row => {
                                row.style.transition = 'all 0.4s ease';
                                row.style.opacity = '0';
                                row.style.height = '0';
                                row.style.padding = '0';
                                setTimeout(() => row.remove(), 400);
                            });
                            
                            setTimeout(() => {
                                checkEmptyAlertState();
                            }, 450);
                        }
                    })
                    .catch(err => {
                        toastr.error('Erro ao marcar todos como lidos.');
                        console.error(err);
                    });
                });
            }

            // Função para verificar se a lista de alertas ficou vazia e injetar o empty state
            function checkEmptyAlertState() {
                const container = document.getElementById('alerts-list-container');
                const rows = container.querySelectorAll('.alert-item-row');
                
                if (rows.length === 0) {
                    // Remove botão no header caso exista
                    const btnHeader = document.getElementById('btn-read-all-alerts');
                    if (btnHeader) btnHeader.remove();

                    // Injeta html do empty state
                    container.innerHTML = `
                        <div class="text-center p-5 text-muted" id="empty-alerts-state">
                            <i class="fa-solid fa-circle-check text-success fa-3x mb-3"></i>
                            <p class="mb-0 fw-bold">Tudo em ordem!</p>
                            <p class="text-muted fs-8 mb-0">Você não possui alertas ou prazos pendentes.</p>
                        </div>
                    `;

                    // Atualiza o contador de alertas no badge da Navbar (caso exista no header)
                    const navBadge = document.querySelector('.navbar-nav .badge');
                    if (navBadge) {
                        navBadge.remove();
                    }
                    
                    const navHeader = document.querySelector('.dropdown-menu .dropdown-header');
                    if (navHeader) {
                        navHeader.textContent = '0 Alertas Não Lidos';
                    }

                    const navMenu = document.querySelector('.dropdown-menu');
                    if (navMenu) {
                        navMenu.innerHTML = `
                            <span class="dropdown-item dropdown-header text-uppercase fs-7 text-secondary py-2">
                                0 Alertas Não Lidos
                            </span>
                            <div class="dropdown-divider mb-0"></div>
                            <div class="dropdown-item text-center text-muted py-3">
                                <i class="fa-solid fa-circle-check text-success mb-2 fs-4"></i>
                                <p class="mb-0 fs-7">Nenhuma pendência encontrada</p>
                            </div>
                        `;
                    }
                }
            }
        });
    </script>
@endpush
