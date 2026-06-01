@extends('layouts.app')

@section('page-title', 'Dashboard')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
@endsection

@section('content')
    <!-- Saudação Inicial -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-primary text-white shadow-sm border-0">
                <div class="card-body p-4">
                    <h4 class="mb-1">Olá, {{ auth()->user()->name }}!</h4>
                    <p class="mb-0 opacity-75">
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
                <div class="small-box card text-bg-primary mb-4 p-3 shadow-sm border-0">
                    <div class="inner">
                        <h3>{{ $stats['companies'] }}</h3>
                        <p>Empresas Contratantes</p>
                    </div>
                    <div class="icon fs-1 opacity-25 position-absolute end-0 bottom-0 me-3">
                        <i class="fa-solid fa-building"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box card text-bg-success mb-4 p-3 shadow-sm border-0">
                    <div class="inner">
                        <h3>{{ $stats['providers'] }}</h3>
                        <p>Fornecedores Ativos</p>
                    </div>
                    <div class="icon fs-1 opacity-25 position-absolute end-0 bottom-0 me-3">
                        <i class="fa-solid fa-handshake"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box card text-bg-warning mb-4 p-3 shadow-sm text-white border-0">
                    <div class="inner">
                        <h3>{{ $stats['contracts'] }}</h3>
                        <p>Contratos Cadastrados</p>
                    </div>
                    <div class="icon fs-1 opacity-25 position-absolute end-0 bottom-0 me-3">
                        <i class="fa-solid fa-file-contract"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box card text-bg-danger mb-4 p-3 shadow-sm border-0">
                    <div class="inner">
                        <h3>{{ $stats['documents'] }}</h3>
                        <p>Documentos Exigidos</p>
                    </div>
                    <div class="icon fs-1 opacity-25 position-absolute end-0 bottom-0 me-3">
                        <i class="fa-solid fa-check-double"></i>
                    </div>
                </div>
            </div>
        @elseif(auth()->user()->isGestor())
            <!-- Widgets para Gestor da Empresa -->
            <div class="col-lg-3 col-6">
                <div class="small-box card text-bg-info mb-4 p-3 shadow-sm text-white border-0">
                    <div class="inner">
                        <h3>{{ $stats['active_contracts'] }}</h3>
                        <p>Contratos Ativos</p>
                    </div>
                    <div class="icon fs-1 opacity-25 position-absolute end-0 bottom-0 me-3">
                        <i class="fa-solid fa-file-signature"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box card text-bg-warning mb-4 p-3 shadow-sm text-white border-0">
                    <div class="inner">
                        <h3>{{ $stats['submitted_documents'] }}</h3>
                        <p>Documentos em Análise</p>
                    </div>
                    <div class="icon fs-1 opacity-25 position-absolute end-0 bottom-0 me-3">
                        <i class="fa-solid fa-hourglass-half"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box card text-bg-danger mb-4 p-3 shadow-sm border-0">
                    <div class="inner">
                        <h3>{{ $stats['pending_documents'] }}</h3>
                        <p>Documentos Pendentes</p>
                    </div>
                    <div class="icon fs-1 opacity-25 position-absolute end-0 bottom-0 me-3">
                        <i class="fa-solid fa-circle-exclamation"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box card text-bg-success mb-4 p-3 shadow-sm border-0">
                    <div class="inner">
                        <h3>{{ $stats['approved_documents'] }}</h3>
                        <p>Documentos Aprovados</p>
                    </div>
                    <div class="icon fs-1 opacity-25 position-absolute end-0 bottom-0 me-3">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                </div>
            </div>
        @else
            <!-- Widgets para Fornecedor -->
            <div class="col-lg-4 col-12">
                <div class="small-box card text-bg-danger mb-4 p-3 shadow-sm border-0">
                    <div class="inner">
                        <h3>{{ $stats['pending_obligations'] }}</h3>
                        <p>Obrigações Pendentes</p>
                    </div>
                    <div class="icon fs-1 opacity-25 position-absolute end-0 bottom-0 me-3">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-6">
                <div class="small-box card text-bg-warning mb-4 p-3 shadow-sm text-white border-0">
                    <div class="inner">
                        <h3>{{ $stats['submitted_documents'] }}</h3>
                        <p>Enviados em Análise</p>
                    </div>
                    <div class="icon fs-1 opacity-25 position-absolute end-0 bottom-0 me-3">
                        <i class="fa-solid fa-paper-plane"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-6">
                <div class="small-box card text-bg-success mb-4 p-3 shadow-sm border-0">
                    <div class="inner">
                        <h3>{{ $stats['compliant_documents'] }}</h3>
                        <p>Documentos em Conformidade</p>
                    </div>
                    <div class="icon fs-1 opacity-25 position-absolute end-0 bottom-0 me-3">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
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
