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

    <!-- Widgets Estatísticos Baseados no Perfil -->
    <div class="row">
        @if(auth()->user()->isSuperAdmin())
            <!-- Widgets para Super Admin -->
            <div class="col-lg-3 col-6">
                <div class="small-box card text-bg-primary mb-4 p-3 shadow-sm">
                    <div class="inner">
                        <h3>2</h3>
                        <p>Empresas Contratantes</p>
                    </div>
                    <div class="icon fs-1 opacity-25 position-absolute end-0 bottom-0 me-3">
                        <i class="fa-solid fa-building"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box card text-bg-success mb-4 p-3 shadow-sm">
                    <div class="inner">
                        <h3>2</h3>
                        <p>Fornecedores Ativos</p>
                    </div>
                    <div class="icon fs-1 opacity-25 position-absolute end-0 bottom-0 me-3">
                        <i class="fa-solid fa-truck"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box card text-bg-warning mb-4 p-3 shadow-sm text-white">
                    <div class="inner">
                        <h3>5</h3>
                        <p>Contratos Cadastrados</p>
                    </div>
                    <div class="icon fs-1 opacity-25 position-absolute end-0 bottom-0 me-3">
                        <i class="fa-solid fa-file-contract"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box card text-bg-danger mb-4 p-3 shadow-sm">
                    <div class="inner">
                        <h3>10</h3>
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
                <div class="small-box card text-bg-info mb-4 p-3 shadow-sm text-white">
                    <div class="inner">
                        <h3>3</h3>
                        <p>Contratos Ativos</p>
                    </div>
                    <div class="icon fs-1 opacity-25 position-absolute end-0 bottom-0 me-3">
                        <i class="fa-solid fa-file-signature"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box card text-bg-warning mb-4 p-3 shadow-sm text-white">
                    <div class="inner">
                        <h3>4</h3>
                        <p>Documentos em Análise</p>
                    </div>
                    <div class="icon fs-1 opacity-25 position-absolute end-0 bottom-0 me-3">
                        <i class="fa-solid fa-hourglass-half"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box card text-bg-danger mb-4 p-3 shadow-sm">
                    <div class="inner">
                        <h3>2</h3>
                        <p>Documentos Pendentes</p>
                    </div>
                    <div class="icon fs-1 opacity-25 position-absolute end-0 bottom-0 me-3">
                        <i class="fa-solid fa-circle-exclamation"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box card text-bg-success mb-4 p-3 shadow-sm">
                    <div class="inner">
                        <h3>12</h3>
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
                <div class="small-box card text-bg-danger mb-4 p-3 shadow-sm">
                    <div class="inner">
                        <h3>3</h3>
                        <p>Obrigações Documentais Pendentes</p>
                    </div>
                    <div class="icon fs-1 opacity-25 position-absolute end-0 bottom-0 me-3">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-6">
                <div class="small-box card text-bg-warning mb-4 p-3 shadow-sm text-white">
                    <div class="inner">
                        <h3>1</h3>
                        <p>Enviados em Análise</p>
                    </div>
                    <div class="icon fs-1 opacity-25 position-absolute end-0 bottom-0 me-3">
                        <i class="fa-solid fa-paper-plane"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-6">
                <div class="small-box card text-bg-success mb-4 p-3 shadow-sm">
                    <div class="inner">
                        <h3>8</h3>
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
        <div class="col-md-8">
            <div class="card shadow-sm mb-4">
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
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Nº Contrato</th>
                                    <th>Objeto</th>
                                    @if(!auth()->user()->isFornecedor())
                                        <th>Fornecedor</th>
                                    @endif
                                    <th>Início</th>
                                    <th>Término</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>CTR-2026-001</strong></td>
                                    <td>Prestação de Serviços de Limpeza e Conservação</td>
                                    @if(!auth()->user()->isFornecedor())
                                        <td>Fornecedor Beta</td>
                                    @endif
                                    <td>01/01/2026</td>
                                    <td>31/12/2026</td>
                                    <td class="text-center"><span class="badge bg-success">Ativo</span></td>
                                </tr>
                                <tr>
                                    <td><strong>CTR-2026-002</strong></td>
                                    <td>Segurança Patrimonial e Monitoramento de Câmeras</td>
                                    @if(!auth()->user()->isFornecedor())
                                        <td>Vigilância Gama</td>
                                    @endif
                                    <td>15/01/2026</td>
                                    <td>14/01/2027</td>
                                    <td class="text-center"><span class="badge bg-success">Ativo</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Informativos / Prazos -->
            <div class="card shadow-sm mb-4">
                <div class="card-header border-0 bg-white py-3">
                    <h5 class="mb-0 text-secondary">
                        <i class="fa-solid fa-bell me-2 text-warning"></i>
                        Alertas de Prazos
                    </h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-start px-0 border-bottom-0 pb-3">
                            <div class="ms-2 me-auto">
                                <div class="fw-bold text-danger">Guia de FGTS (Vencimento)</div>
                                <span class="text-muted fs-7">Obrigação mensal do Fornecedor Beta</span>
                            </div>
                            <span class="badge bg-danger rounded-pill">Atrasado</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-start px-0 border-bottom-0 pb-3">
                            <div class="ms-2 me-auto">
                                <div class="fw-bold text-warning">CND Trabalhista</div>
                                <span class="text-muted fs-7">Vencimento em 15 dias para Vigilância Gama</span>
                            </div>
                            <span class="badge bg-warning text-dark rounded-pill">A vencer</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection
