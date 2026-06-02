@extends('layouts.app')

@section('page-title', 'Detalhes do Contrato: ' . $contract->contract_number)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('contracts.index') }}">Contratos</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $contract->contract_number }}</li>
@endsection

@section('content')
    <!-- 1. Cabeçalho Geral do Contrato -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                        <div>
                            <span class="badge @if($contract->status === 'active') bg-success @elseif($contract->status === 'draft') bg-secondary @elseif($contract->status === 'suspended') bg-warning text-dark @else bg-danger @endif mb-2 px-3 py-2 fs-7">
                                @switch($contract->status)
                                    @case('active') Ativo @break
                                    @case('draft') Rascunho @break
                                    @case('suspended') Suspenso @break
                                    @case('expired') Vencido @break
                                @endswitch
                            </span>
                            <h3 class="mb-1 fw-bold text-dark">{{ $contract->title }}</h3>
                            <p class="text-muted mb-0">Contrato Nº: <strong>{{ $contract->contract_number }}</strong> | Empresa: <strong>{{ $contract->company->name }}</strong></p>
                        </div>
                        <div class="text-end">
                            <p class="text-muted mb-1 fs-7">Vigência:</p>
                            <h5 class="fw-bold mb-0 text-secondary">
                                <i class="fa-solid fa-calendar-days me-1 text-primary"></i> 
                                {{ $contract->start_date->format('d/m/Y') }} - {{ $contract->end_date->format('d/m/Y') }}
                            </h5>
                        </div>
                    </div>
                    <hr class="my-3">
                    <div class="row text-center text-md-start">
                        <div class="col-md-4 col-12 mb-3 mb-md-0">
                            <span class="text-muted fs-8 d-block text-uppercase">Fornecedor Contratado</span>
                            @if($contract->provider)
                                <strong class="text-dark"><i class="fa-solid fa-handshake me-1 text-primary"></i> {{ $contract->provider->name }}</strong>
                                <small class="d-block text-muted">CNPJ: {{ $contract->provider->cnpj }}</small>
                            @else
                                <strong class="text-secondary"><i class="fa-solid fa-handshake me-1 text-muted"></i> Controle Interno</strong>
                                <small class="d-block text-muted">Sem fornecedor atribuído</small>
                            @endif
                        </div>
                        <div class="col-md-4 col-12 mb-3 mb-md-0">
                            <span class="text-muted fs-8 d-block text-uppercase">Gestor Responsável</span>
                            <strong class="text-dark"><i class="fa-solid fa-user-tie me-1 text-primary"></i> {{ $contract->responsible->name ?? 'N/A' }}</strong>
                            <small class="d-block text-muted">E-mail: {{ $contract->responsible->email ?? 'N/A' }}</small>
                        </div>
                        <div class="col-md-4 col-12">
                            <span class="text-muted fs-8 d-block text-uppercase">Configuração de Alerta</span>
                            <strong class="text-dark"><i class="fa-solid fa-bell me-1 text-warning"></i> Notificar {{ $contract->alert_days }} dias antes</strong>
                            <small class="d-block text-muted">Enviado por e-mail automaticamente</small>
                        </div>
                    </div>
                    @if($contract->description)
                        <div class="mt-3 bg-light p-3 rounded fs-7 text-secondary">
                            <strong>Descrição do Objeto:</strong> {{ $contract->description }}
                        </div>
                    @endif

                    <!-- Painel de Validação de Assinatura -->
                    <div class="mt-3 p-3 border rounded @if($contract->signature_validated) bg-success-subtle border-success-subtle @else bg-warning-subtle border-warning-subtle @endif d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div class="fs-7">
                            @if($contract->signature_validated)
                                <span class="text-success"><i class="fa-solid fa-circle-check me-1"></i> <strong>Assinatura Confirmada:</strong> A assinatura deste contrato foi validada e confirmada.</span>
                            @else
                                <span class="text-warning-emphasis"><i class="fa-solid fa-circle-exclamation me-1"></i> <strong>Assinatura Pendente:</strong> Aguardando validação de assinatura (coleta de firmas).</span>
                            @endif
                        </div>
                        @if(!auth()->user()->isFornecedor() && !$contract->signature_validated)
                            <form action="{{ route('contracts.validate-signature', $contract) }}" method="POST" class="m-0">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-success">
                                    <i class="fa-solid fa-signature me-1"></i> Validar/Confirmar Assinatura
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Stepper Horizontal (Andamento do Contrato) -->
    @php
        // Lógica de determinação da etapa ativa no Stepper
        // Nova ordem: 1-Elaboração | 2-Assinatura | 3-Compliance GED | 4-Vigência Ativa | 5-Encerrado
        $hasPendingDocs = $contract->documents->contains(fn($doc) => in_array($doc->status, ['pending', 'rejected']));
        $hasSubmittedDocs = $contract->documents->contains('status', 'submitted');

        $step1 = 'completed'; // Elaboração sempre concluída
        $step2 = 'inactive';  // Assinatura
        $step3 = 'inactive';  // Compliance GED
        $step4 = 'inactive';  // Vigência Ativa
        $step5 = 'inactive';  // Encerrado / Suspenso

        if ($contract->status === 'draft') {
            $step1 = 'active';
        } elseif ($contract->status === 'suspended' || $contract->status === 'expired') {
            $step1 = 'completed';
            $step2 = 'completed';
            $step3 = 'completed';
            $step4 = 'completed';
            $step5 = $contract->status === 'suspended' ? 'warning' : 'danger';
        } elseif ($contract->status === 'active') {
            $step1 = 'completed';
            $step2 = 'completed';
            $step3 = 'completed';
            $step4 = 'active';
        } else { // em análise / vigente normal
            // Passo 2: Assinatura
            if (!$contract->signature_validated) {
                $step2 = 'active';
            } else {
                $step2 = 'completed';
                // Passo 3: GED só começa depois da assinatura
                if ($hasPendingDocs || $hasSubmittedDocs) {
                    $step3 = 'active';
                } else {
                    $step3 = 'completed';
                }
            }
        }
    @endphp
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="fw-bold mb-0 text-secondary"><i class="fa-solid fa-route me-2 text-primary"></i> Andamento do Contrato</h6>
                </div>
                <div class="card-body py-4">
                    <ul class="stepper-horizontal">
                        <!-- Passo 1: Elaboração -->
                        <li class="stepper-item {{ $step1 }}">
                            <div class="stepper-circle">
                                @if($step1 === 'completed') <i class="fa-solid fa-check"></i> @else 1 @endif
                            </div>
                            <div class="stepper-title">Elaboração</div>
                            <div class="stepper-desc">Rascunho criado</div>
                        </li>
                        <!-- Passo 2: Assinatura -->
                        <li class="stepper-item {{ $step2 }}">
                            <div class="stepper-circle">
                                @if($step2 === 'completed') <i class="fa-solid fa-check"></i> @else 2 @endif
                            </div>
                            <div class="stepper-title">Assinatura</div>
                            <div class="stepper-desc">
                                @if($contract->signature_validated)
                                    Assinatura Confirmada
                                @else
                                    Coleta de Firmas
                                @endif
                            </div>
                        </li>
                        <!-- Passo 3: Documentos (GED) -->
                        <li class="stepper-item {{ $step3 }}">
                            <div class="stepper-circle">
                                @if($step3 === 'completed') <i class="fa-solid fa-check"></i> @else 3 @endif
                            </div>
                            <div class="stepper-title">Compliance GED</div>
                            <div class="stepper-desc">
                                @if($hasPendingDocs)
                                    Aguardando Uploads
                                @elseif($hasSubmittedDocs)
                                    Documentos em Análise
                                @else
                                    Documentos Aprovados
                                @endif
                            </div>
                        </li>
                        <!-- Passo 4: Ativo -->
                        <li class="stepper-item {{ $step4 }}">
                            <div class="stepper-circle">
                                @if($step4 === 'completed') <i class="fa-solid fa-check"></i> @else 4 @endif
                            </div>
                            <div class="stepper-title">Vigência Ativa</div>
                            <div class="stepper-desc">Contrato em vigor</div>
                        </li>
                        <!-- Passo 5: Fim / Suspenso -->
                        <li class="stepper-item {{ $step5 }}">
                            <div class="stepper-circle">
                                @if($step5 === 'warning') 
                                    <i class="fa-solid fa-pause"></i> 
                                @elseif($step5 === 'danger') 
                                    <i class="fa-solid fa-ban"></i> 
                                @else 
                                    5 
                                @endif
                            </div>
                            <div class="stepper-title">Encerrado / Suspenso</div>
                            <div class="stepper-desc">Fim do ciclo</div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Linha do Tempo Horizontal (Histórico Completo) -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="fw-bold mb-0 text-secondary">
                        <i class="fa-solid fa-clock-rotate-left me-2 text-primary"></i> 
                        Linha do Tempo (Histórico do Contrato)
                    </h6>
                </div>
                <div class="card-body p-0">
                    @if($contract->histories->isEmpty())
                        <div class="text-center p-5">
                            <i class="fa-solid fa-timeline fa-2x text-muted mb-2"></i>
                            <p class="text-muted mb-0 fs-7">Nenhum evento registrado na linha do tempo ainda.</p>
                        </div>
                    @else
                        <div class="timeline-horizontal-wrapper">
                            <div class="timeline-horizontal-container">
                                @foreach($contract->histories as $history)
                                    @php
                                        // Mapear ícone e classe de badge com base na ação
                                        $badgeIcon = 'fa-solid fa-circle-notch';
                                        $badgeBg = 'bg-secondary';
                                        switch($history->action) {
                                            case 'whatsapp_charge':
                                                $badgeIcon = 'fa-brands fa-whatsapp';
                                                $badgeBg = 'bg-success';
                                                break;
                                            case 'signature_validated':
                                                $badgeIcon = 'fa-solid fa-signature';
                                                $badgeBg = 'bg-success';
                                                break;
                                            case 'created':
                                                $badgeIcon = 'fa-solid fa-plus';
                                                $badgeBg = 'bg-primary';
                                                break;
                                            case 'updated':
                                                $badgeIcon = 'fa-solid fa-pen';
                                                $badgeBg = 'bg-info';
                                                break;
                                            case 'status_changed':
                                                $badgeIcon = 'fa-solid fa-arrows-spin';
                                                $badgeBg = 'bg-warning text-dark';
                                                break;
                                            case 'document_submitted':
                                                $badgeIcon = 'fa-solid fa-upload';
                                                $badgeBg = 'bg-purple';
                                                break;
                                            case 'document_approved':
                                                $badgeIcon = 'fa-solid fa-check';
                                                $badgeBg = 'bg-success';
                                                break;
                                            case 'document_rejected':
                                                $badgeIcon = 'fa-solid fa-xmark';
                                                $badgeBg = 'bg-danger';
                                                break;
                                            case 'request_opened':
                                                $badgeIcon = 'fa-solid fa-envelope';
                                                $badgeBg = 'bg-teal';
                                                break;
                                            case 'request_resolved':
                                                $badgeIcon = 'fa-solid fa-circle-check';
                                                $badgeBg = 'bg-success';
                                                break;
                                            case 'request_rejected':
                                                $badgeIcon = 'fa-solid fa-ban';
                                                $badgeBg = 'bg-danger';
                                                break;
                                        }
                                    @endphp
                                    <div class="timeline-horizontal-item">
                                        <div class="timeline-horizontal-badge {{ $badgeBg }}">
                                            <i class="{{ $badgeIcon }}"></i>
                                        </div>
                                        <div class="timeline-horizontal-card">
                                            <div class="timeline-horizontal-date">
                                                {{ $history->created_at->format('d/m/Y H:i') }}
                                            </div>
                                            <div class="timeline-horizontal-title">
                                                {{ $history->title }}
                                            </div>
                                            <div class="timeline-horizontal-desc">
                                                {{ $history->description }}
                                                @if($history->user)
                                                    <span class="d-block mt-1 font-weight-bold text-muted fs-8 text-end">
                                                        Por: {{ $history->user->name }}
                                                    </span>
                                                @endif
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

    <!-- Seção de Ações: Solicitações e GED lado a lado -->
    <div class="row">
        <!-- 4. Painel de Solicitações Bi-direcionais -->
        <div class="col-lg-6 col-12 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-secondary">
                        <i class="fa-solid fa-comments me-2 text-primary"></i> 
                        Solicitações Bi-direcionais
                    </h6>
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#createRequestModal">
                        <i class="fa-solid fa-paper-plane me-1"></i> Nova Solicitação
                    </button>
                </div>
                <div class="card-body">
                    <!-- Nav Tabs para organizar solicitações -->
                    <ul class="nav nav-pills mb-3" id="requestsTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active px-3 py-1.5 fs-7" id="received-tab" data-bs-toggle="pill" data-bs-target="#received-reqs" type="button" role="tab" aria-controls="received-reqs" aria-selected="true">
                                <i class="fa-solid fa-inbox me-1"></i> Recebidas
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link px-3 py-1.5 fs-7" id="sent-tab" data-bs-toggle="pill" data-bs-target="#sent-reqs" type="button" role="tab" aria-controls="sent-reqs" aria-selected="false">
                                <i class="fa-solid fa-paper-plane me-1"></i> Enviadas
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content" id="requestsTabContent">
                        <!-- Solicitações Recebidas -->
                        <div class="tab-pane fade show active" id="received-reqs" role="tabpanel" aria-labelledby="received-tab">
                            @php
                                $received = $contract->requests->filter(function($req) {
                                    if (auth()->user()->isFornecedor()) {
                                        return $req->sender_type === 'company';
                                    }
                                    return $req->sender_type === 'provider';
                                });
                            @endphp

                            @if($received->isEmpty())
                                <div class="text-center py-5 text-muted">
                                    <i class="fa-solid fa-envelope-open fa-2x mb-2 opacity-50"></i>
                                    <p class="mb-0 fs-7">Nenhuma solicitação recebida para este contrato.</p>
                                </div>
                            @else
                                <div class="accordion" id="accordionReceived">
                                    @foreach($received as $req)
                                        <div class="accordion-item border shadow-2xs mb-2 rounded">
                                            <h2 class="accordion-header" id="headingRec{{ $req->id }}">
                                                <button class="accordion-button collapsed py-2 px-3 fs-7" type="button" data-bs-toggle="collapse" data-bs-target="#collapseRec{{ $req->id }}" aria-expanded="false" aria-controls="collapseRec{{ $req->id }}">
                                                    <span class="badge @if($req->status === 'pending') bg-warning text-dark @elseif($req->status === 'resolved') bg-success @else bg-danger @endif me-2">
                                                        @switch($req->status)
                                                            @case('pending') Pendente @break
                                                            @case('in_analysis') Em Análise @break
                                                            @case('resolved') Resolvido @break
                                                            @case('rejected') Recusado @break
                                                        @endswitch
                                                    </span>
                                                    <strong class="text-dark">{{ $req->title }}</strong>
                                                    <small class="text-muted ms-auto pe-2">{{ $req->created_at->format('d/m/Y') }}</small>
                                                </button>
                                            </h2>
                                            <div id="collapseRec{{ $req->id }}" class="accordion-collapse collapse" aria-labelledby="headingRec{{ $req->id }}" data-bs-parent="#accordionReceived">
                                                <div class="accordion-body bg-light fs-7 p-3">
                                                    @if($req->due_date)
                                                        @php
                                                            $isExpired = $req->status === 'pending' && $req->due_date->isPast();
                                                        @endphp
                                                        <span class="badge @if($isExpired) bg-danger @else bg-info @endif mb-2">
                                                            <i class="fa-solid fa-calendar-day me-1"></i> Prazo: {{ $req->due_date->format('d/m/Y') }} @if($isExpired) (Atrasado) @endif
                                                        </span>
                                                    @endif
                                                    @if($req->requires_attachment)
                                                        <span class="badge bg-warning text-dark mb-2">
                                                            <i class="fa-solid fa-paperclip me-1"></i> Exige anexo de retorno
                                                        </span>
                                                    @endif

                                                    <p class="mb-2"><strong>Descrição:</strong><br>{{ $req->description }}</p>

                                                    @if($req->file_path)
                                                        <div class="mb-2">
                                                            <a href="{{ route('contracts.requests.download', [$req, 'sender']) }}" class="btn btn-xs btn-outline-primary py-1 px-2.5 fs-8">
                                                                <i class="fa-solid fa-download me-1"></i> Baixar Anexo da Solicitação ({{ $req->original_name }})
                                                            </a>
                                                        </div>
                                                    @endif

                                                    <p class="mb-1 text-muted fs-8">Enviada por: {{ $req->user->name }} ({{ $req->sender_type === 'company' ? 'Empresa Contratante' : 'Fornecedor' }})</p>
                                                    
                                                    @if($req->status === 'pending')
                                                        <!-- Formulário de Resposta -->
                                                        <hr class="my-2">
                                                        <form action="{{ route('contracts.requests.respond', $req) }}" method="POST" enctype="multipart/form-data">
                                                            @csrf
                                                            <div class="mb-2">
                                                                <label for="response_text_{{ $req->id }}" class="form-label fw-bold mb-1">Escrever Resposta / Parecer:</label>
                                                                <textarea name="response_text" id="response_text_{{ $req->id }}" rows="3" class="form-control form-control-sm" placeholder="Responda de forma objetiva ou justifique a rejeição..." required></textarea>
                                                            </div>
                                                            <div class="mb-2">
                                                                <label for="response_file_{{ $req->id }}" class="form-label fw-bold mb-1">
                                                                    Anexar Documento de Resposta @if($req->requires_attachment) <span class="text-danger">* (Obrigatório)</span> @else (Opcional) @endif:
                                                                </label>
                                                                <input type="file" name="response_file" id="response_file_{{ $req->id }}" class="form-control form-control-sm" accept=".pdf,.jpg,.jpeg,.png" @if($req->requires_attachment) required @endif>
                                                            </div>
                                                            <div class="d-flex gap-2">
                                                                <button type="submit" name="status" value="resolved" class="btn btn-sm btn-success py-1 px-3">
                                                                    <i class="fa-solid fa-circle-check me-1"></i> Resolver / Aprovar
                                                                </button>
                                                                <button type="submit" name="status" value="rejected" class="btn btn-sm btn-danger py-1 px-3">
                                                                    <i class="fa-solid fa-circle-xmark me-1"></i> Rejeitar / Recusar
                                                                </button>
                                                            </div>
                                                        </form>
                                                    @else
                                                        <hr class="my-2">
                                                        <div class="bg-white p-2 border rounded">
                                                            <span class="badge @if($req->status === 'resolved') bg-success-subtle text-success @else bg-danger-subtle text-danger @endif text-uppercase fs-9 mb-1 d-inline-block">Parecer</span>
                                                            <p class="mb-1 text-dark"><strong>Resposta:</strong> {{ $req->response_text }}</p>
                                                            
                                                            @if($req->response_file_path)
                                                                <div class="mt-2 mb-1">
                                                                    <a href="{{ route('contracts.requests.download', [$req, 'responder']) }}" class="btn btn-xs btn-outline-success py-1 px-2.5 fs-8">
                                                                        <i class="fa-solid fa-download me-1"></i> Baixar Anexo de Resposta ({{ $req->response_original_name }})
                                                                    </a>
                                                                </div>
                                                            @endif
                                                            
                                                            <small class="text-muted fs-8 d-block">Respondido por: {{ $req->responder->name ?? 'N/A' }} em {{ $req->responded_at->format('d/m/Y H:i') }}</small>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <!-- Solicitações Enviadas -->
                        <div class="tab-pane fade" id="sent-reqs" role="tabpanel" aria-labelledby="sent-tab">
                            @php
                                $sent = $contract->requests->filter(function($req) {
                                    if (auth()->user()->isFornecedor()) {
                                        return $req->sender_type === 'provider';
                                    }
                                    return $req->sender_type === 'company';
                                });
                            @endphp

                            @if($sent->isEmpty())
                                <div class="text-center py-5 text-muted">
                                    <i class="fa-solid fa-paper-plane fa-2x mb-2 opacity-50"></i>
                                    <p class="mb-0 fs-7">Você ainda não enviou solicitações para este contrato.</p>
                                </div>
                            @else
                                <div class="accordion" id="accordionSent">
                                    @foreach($sent as $req)
                                        <div class="accordion-item border shadow-2xs mb-2 rounded">
                                            <h2 class="accordion-header" id="headingSent{{ $req->id }}">
                                                <button class="accordion-button collapsed py-2 px-3 fs-7" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSent{{ $req->id }}" aria-expanded="false" aria-controls="collapseSent{{ $req->id }}">
                                                    <span class="badge @if($req->status === 'pending') bg-warning text-dark @elseif($req->status === 'resolved') bg-success @else bg-danger @endif me-2">
                                                        @switch($req->status)
                                                            @case('pending') Pendente @break
                                                            @case('in_analysis') Em Análise @break
                                                            @case('resolved') Resolvido @break
                                                            @case('rejected') Recusado @break
                                                        @endswitch
                                                    </span>
                                                    <strong class="text-dark">{{ $req->title }}</strong>
                                                    <small class="text-muted ms-auto pe-2">{{ $req->created_at->format('d/m/Y') }}</small>
                                                </button>
                                            </h2>
                                            <div id="collapseSent{{ $req->id }}" class="accordion-collapse collapse" aria-labelledby="headingSent{{ $req->id }}" data-bs-parent="#accordionSent">
                                                <div class="accordion-body bg-light fs-7 p-3">
                                                    @if($req->due_date)
                                                        @php
                                                            $isExpired = $req->status === 'pending' && $req->due_date->isPast();
                                                        @endphp
                                                        <span class="badge @if($isExpired) bg-danger @else bg-info @endif mb-2">
                                                            <i class="fa-solid fa-calendar-day me-1"></i> Prazo: {{ $req->due_date->format('d/m/Y') }} @if($isExpired) (Atrasado) @endif
                                                        </span>
                                                    @endif
                                                    @if($req->requires_attachment)
                                                        <span class="badge bg-warning text-dark mb-2">
                                                            <i class="fa-solid fa-paperclip me-1"></i> Exige anexo de retorno
                                                        </span>
                                                    @endif

                                                    <p class="mb-2"><strong>Descrição:</strong><br>{{ $req->description }}</p>

                                                    @if($req->file_path)
                                                        <div class="mb-2">
                                                            <a href="{{ route('contracts.requests.download', [$req, 'sender']) }}" class="btn btn-xs btn-outline-primary py-1 px-2.5 fs-8">
                                                                <i class="fa-solid fa-download me-1"></i> Baixar Anexo da Solicitação ({{ $req->original_name }})
                                                            </a>
                                                        </div>
                                                    @endif

                                                    <span class="text-muted fs-8 d-block">Tipo: 
                                                        @switch($req->type)
                                                            @case('clarification') Esclarecimento @break
                                                            @case('amendment') Aditivo Contratual @break
                                                            @case('renewal') Renovação @break
                                                            @case('document') Ajuste de Documento @break
                                                            @case('other') Outro @break
                                                        @endswitch
                                                    </span>
                                                    @if(!auth()->user()->isFornecedor() && $req->status === 'pending' && $contract->provider_id)
                                                        <div class="mt-2 text-start">
                                                            <button type="button" class="btn btn-xs btn-success btn-whatsapp-notify" data-id="{{ $req->id }}" data-type="request" title="Notificar via WhatsApp">
                                                                <i class="fa-brands fa-whatsapp me-1"></i> Notificar via WhatsApp
                                                            </button>
                                                        </div>
                                                    @endif

                                                    @if($req->status !== 'pending')
                                                        <hr class="my-2">
                                                        <div class="bg-white p-2 border rounded">
                                                            <span class="badge @if($req->status === 'resolved') bg-success-subtle text-success @else bg-danger-subtle text-danger @endif text-uppercase fs-9 mb-1 d-inline-block">Parecer</span>
                                                            <p class="mb-1 text-dark"><strong>Resposta:</strong> {{ $req->response_text }}</p>
                                                            
                                                            @if($req->response_file_path)
                                                                <div class="mt-2 mb-1">
                                                                    <a href="{{ route('contracts.requests.download', [$req, 'responder']) }}" class="btn btn-xs btn-outline-success py-1 px-2.5 fs-8">
                                                                        <i class="fa-solid fa-download me-1"></i> Baixar Anexo de Resposta ({{ $req->response_original_name }})
                                                                    </a>
                                                                </div>
                                                            @endif
                                                            
                                                            <small class="text-muted fs-8 d-block">Respondido por: {{ $req->responder->name ?? 'N/A' }} em {{ $req->responded_at->format('d/m/Y H:i') }}</small>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 5. GED Integrado (Documentos e Compliance) -->
        <div class="col-lg-6 col-12 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-secondary">
                        <i class="fa-solid fa-folder-open me-2 text-primary"></i> 
                        Obrigações Documentais (GED)
                    </h6>
                    @if(!auth()->user()->isFornecedor())
                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addObligationModal">
                            <i class="fa-solid fa-plus me-1"></i> Nova Obrigação
                        </button>
                    @endif
                </div>
                <div class="card-body p-0">
                    @if($contract->documents->isEmpty())
                        <div class="text-center p-5 text-muted">
                            <i class="fa-solid fa-check-double fa-2x mb-2 text-success"></i>
                            <p class="mb-0 fs-7">Nenhum documento exigido para este contrato.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 fs-7">
                                <thead class="table-light">
                                    <tr>
                                        <th>Tipo de Documento</th>
                                        <th>Prazo</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-end px-3">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($contract->documents as $doc)
                                        <tr>
                                            <td>
                                                <div class="fw-bold text-dark">{{ $doc->documentType->name }}</div>
                                                <span class="text-muted fs-9 text-uppercase">
                                                    @switch($doc->documentType->periodicity)
                                                        @case('monthly') Mensal @break
                                                        @case('quarterly') Trimestral @break
                                                        @case('semi-annual') Semestral @break
                                                        @case('annual') Anual @break
                                                        @case('once') Único @break
                                                    @endswitch
                                                </span>
                                            </td>
                                            <td>{{ $doc->due_date->format('d/m/Y') }}</td>
                                            <td class="text-center">
                                                @switch($doc->status)
                                                    @case('pending')
                                                        <span class="badge bg-secondary">Pendente</span>
                                                        @break
                                                    @case('submitted')
                                                        <span class="badge bg-warning text-dark">Em Análise</span>
                                                        @break
                                                    @case('approved')
                                                        <span class="badge bg-success">Aprovado</span>
                                                        @break
                                                    @case('rejected')
                                                        <span class="badge bg-danger">Recusado</span>
                                                        @break
                                                @endswitch
                                            </td>
                                            <td class="text-end px-3">
                                                <div class="d-flex justify-content-end gap-1">
                                                    <!-- Visualizar arquivo enviado -->
                                                    @if($doc->file_path)
                                                        <a href="{{ route('ged.download', $doc) }}" class="btn btn-xs btn-outline-secondary py-0.5 px-1.5" title="Baixar PDF">
                                                            <i class="fa-solid fa-download"></i>
                                                        </a>
                                                    @endif

                                                    @if(auth()->user()->isFornecedor())
                                                        <!-- Upload form inline para o fornecedor -->
                                                        @if(in_array($doc->status, ['pending', 'rejected']))
                                                            <button type="button" class="btn btn-xs btn-primary py-0.5 px-1.5 btn-upload-doc" data-id="{{ $doc->id }}" data-url="{{ route('ged.upload', $doc) }}" data-name="{{ $doc->documentType->name }}">
                                                                <i class="fa-solid fa-upload"></i> Enviar
                                                            </button>
                                                        @endif
                                                    @else
                                                        <!-- Ações rápidas de aprovação para o Gestor -->
                                                        @if($doc->status === 'submitted')
                                                            <form action="{{ route('ged.approve', $doc) }}" method="POST" class="d-inline form-approve-inline">
                                                                @csrf
                                                                <button type="submit" class="btn btn-xs btn-success py-0.5 px-1.5" title="Aprovar Documento">
                                                                    <i class="fa-solid fa-check"></i>
                                                                </button>
                                                            </form>
                                                            <button type="button" class="btn btn-xs btn-danger py-0.5 px-1.5 btn-reject-doc" data-id="{{ $doc->id }}" data-url="{{ route('ged.reject', $doc) }}" data-name="{{ $doc->documentType->name }}">
                                                                <i class="fa-solid fa-xmark"></i>
                                                            </button>
                                                        @endif
                                                        @if(in_array($doc->status, ['pending', 'rejected']) && $contract->provider_id)
                                                            <button type="button" class="btn btn-xs btn-success py-0.5 px-1.5 btn-whatsapp-notify" data-id="{{ $doc->id }}" data-type="document" title="Cobrar Envio via WhatsApp">
                                                                <i class="fa-brands fa-whatsapp"></i> Cobrar
                                                            </button>
                                                        @endif
                                                    @endif
                                                </div>

                                                <!-- Rejection reason warning tooltip -->
                                                @if($doc->status === 'rejected' && $doc->rejection_reason)
                                                    <div class="text-danger fs-9 mt-1 text-end">
                                                        <i class="fa-solid fa-triangle-exclamation"></i> <strong>Recusa:</strong> {{ $doc->rejection_reason }}
                                                    </div>
                                                @endif
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

    <!-- MODAIS NECESSÁRIOS -->

    <!-- 1. Modal: Nova Solicitação -->
    <div class="modal fade" id="createRequestModal" tabindex="-1" aria-labelledby="createRequestModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('contracts.requests.store', $contract) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="createRequestModalLabel">Nova Solicitação / Chamado</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Tipo de Solicitação -->
                        <div class="mb-3">
                            <label for="type" class="form-label fw-bold">Tipo:</label>
                            <select name="type" id="type" class="form-select" required>
                                <option value="clarification">Esclarecimento de Dúvida</option>
                                <option value="amendment">Solicitação de Aditivo</option>
                                <option value="renewal">Solicitação de Renovação</option>
                                <option value="document">Ajuste / Correção de Documento</option>
                                <option value="other">Outro Assunto</option>
                            </select>
                        </div>
                        <!-- Título -->
                        <div class="mb-3">
                            <label for="title" class="form-label fw-bold">Assunto / Título:</label>
                            <input type="text" name="title" id="title" class="form-control" placeholder="Título resumido da solicitação..." required>
                        </div>
                        <!-- Descrição -->
                        <div class="mb-3">
                            <label for="description" class="form-label fw-bold">Descrição da Demanda:</label>
                            <textarea name="description" id="description" rows="4" class="form-control" placeholder="Escreva os detalhes, dúvidas ou justificativas..." required></textarea>
                        </div>
                        <!-- Prazo Limite -->
                        <div class="mb-3">
                            <label for="due_date" class="form-label fw-bold">Prazo Limite para Resposta (Opcional):</label>
                            <input type="date" name="due_date" id="due_date" class="form-control" min="{{ date('Y-m-d') }}">
                        </div>
                        <!-- Anexo -->
                        <div class="mb-3">
                            <label for="file" class="form-label fw-bold">Anexar Documento (Opcional - PDF/Imagem):</label>
                            <input type="file" name="file" id="file" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                        </div>
                        <!-- Exigir Anexo de Retorno -->
                        <div class="form-check mb-2">
                            <input type="checkbox" name="requires_attachment" id="requires_attachment" class="form-check-input" value="1">
                            <label for="requires_attachment" class="form-check-label fw-bold">Exigir anexo na resposta da outra parte</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-paper-plane me-1"></i> Enviar Solicitação</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- 4. Modal: Adicionar Obrigação Documental (Gestor) -->
    @if(!auth()->user()->isFornecedor())
        <div class="modal fade" id="addObligationModal" tabindex="-1" aria-labelledby="addObligationModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form action="{{ route('contracts.documents.store', $contract) }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title" id="addObligationModalLabel">Nova Exigência Documental</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p class="fs-7 text-muted">Defina uma nova obrigação de envio para este contrato.</p>
                            <!-- Tipo de Documento -->
                            <div class="mb-3">
                                <label for="document_type_id" class="form-label fw-bold">Tipo de Documento:</label>
                                <select name="document_type_id" id="document_type_id" class="form-select" required>
                                    <option value="" disabled selected>Selecione o tipo de documento...</option>
                                    @foreach($documentTypes as $type)
                                        <option value="{{ $type->id }}">{{ $type->name }} ({{ $type->periodicity == 'once' ? 'Único' : 'Periódico' }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <!-- Prazo -->
                            <div class="mb-3">
                                <label for="due_date_obligation" class="form-label fw-bold">Data Limite de Vencimento:</label>
                                <input type="date" name="due_date" id="due_date_obligation" class="form-control" min="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save me-1"></i> Exigir Documento</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    @if(auth()->user()->isFornecedor())
        <!-- 2. Modal: Upload de Documento -->
        <div class="modal fade" id="uploadDocModal" tabindex="-1" aria-labelledby="uploadDocModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form id="form-upload-doc" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title" id="uploadDocModalLabel">Enviar Documento</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p class="fs-7 text-muted">Você está enviando o documento para a obrigação: <strong id="upload-doc-name"></strong></p>
                            <div class="mb-3">
                                <label for="file" class="form-label fw-bold">Selecione o Arquivo (PDF ou Imagem - Max 10MB):</label>
                                <input type="file" name="file" id="file" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-upload me-1"></i> Fazer Upload</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @else
        <!-- 3. Modal: Rejeição de Documento -->
        <div class="modal fade" id="rejectDocModal" tabindex="-1" aria-labelledby="rejectDocModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form id="form-reject-doc" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title" id="rejectDocModalLabel">Recusar Documento</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p class="fs-7 text-muted">Você está recusando o documento: <strong id="reject-doc-name"></strong></p>
                            <div class="mb-3">
                                <label for="rejection_reason" class="form-label fw-bold">Motivo da Recusa / Ajustes Necessários:</label>
                                <textarea name="rejection_reason" id="rejection_reason" rows="4" class="form-control" placeholder="Descreva os motivos da recusa..." required></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-danger"><i class="fa-solid fa-xmark me-1"></i> Confirmar Recusa</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Modal Upload
            const uploadBtn = document.querySelectorAll('.btn-upload-doc');
            if (uploadBtn.length > 0) {
                const uploadModal = new bootstrap.Modal(document.getElementById('uploadDocModal'));
                const uploadForm = document.getElementById('form-upload-doc');
                const uploadName = document.getElementById('upload-doc-name');

                uploadBtn.forEach(btn => {
                    btn.addEventListener('click', function() {
                        uploadForm.setAttribute('action', this.getAttribute('data-url'));
                        uploadName.textContent = this.getAttribute('data-name');
                        uploadModal.show();
                    });
                });
            }

            // Modal Rejeição
            const rejectBtn = document.querySelectorAll('.btn-reject-doc');
            if (rejectBtn.length > 0) {
                const rejectModal = new bootstrap.Modal(document.getElementById('rejectDocModal'));
                const rejectForm = document.getElementById('form-reject-doc');
                const rejectName = document.getElementById('reject-doc-name');

                rejectBtn.forEach(btn => {
                    btn.addEventListener('click', function() {
                        rejectForm.setAttribute('action', this.getAttribute('data-url'));
                        rejectName.textContent = this.getAttribute('data-name');
                        document.getElementById('rejection_reason').value = '';
                        rejectModal.show();
                    });
                });
            }

            // Confirmação SweetAlert de Aprovação Inline
            document.querySelectorAll('.form-approve-inline').forEach(form => {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Aprovar Documento?',
                        text: "O documento do fornecedor será marcado em conformidade.",
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#28a745',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Sim, aprovar!',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });

            // Lógica do Modal WhatsApp
            const waModalEl = document.getElementById('whatsappNotifyModal');
            if (waModalEl) {
                const waModal = new bootstrap.Modal(waModalEl);
                const waResourceId = document.getElementById('wa-resource-id');
                const waResourceType = document.getElementById('wa-resource-type');

                document.querySelectorAll('.btn-whatsapp-notify').forEach(button => {
                    button.addEventListener('click', function() {
                        waResourceId.value = this.getAttribute('data-id');
                        waResourceType.value = this.getAttribute('data-type');
                        waModal.show();
                    });
                });

                document.querySelectorAll('.btn-select-wa-contact').forEach(button => {
                    button.addEventListener('click', function() {
                        const phone = this.getAttribute('data-phone');
                        const id = waResourceId.value;
                        const type = waResourceType.value;
                        
                        if (!phone) {
                            toastr.error('Este contato não possui telefone cadastrado.');
                            return;
                        }

                        const url = type === 'request' 
                            ? `/contracts/requests/${id}/whatsapp-link`
                            : `/contracts/documents/${id}/whatsapp-link`;

                        fetch(url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        })
                        .then(response => response.json())
                        .then(res => {
                            if (res.status === 'success') {
                                waModal.hide();
                                toastr.success('Link mágico gerado com sucesso!');
                                
                                const text = encodeURIComponent(res.message);
                                const waUrl = `https://api.whatsapp.com/send?phone=${phone}&text=${text}`;
                                
                                window.open(waUrl, '_blank');
                            } else {
                                toastr.error('Erro ao gerar link de notificação.');
                            }
                        })
                        .catch(err => {
                            toastr.error('Falha na comunicação com o servidor.');
                            console.error(err);
                        });
                    });
                });
            }
        });
    </script>
@endpush

@if($contract->provider)
<!-- MODAL DE SELEÇÃO DE CONTATO WHATSAPP -->
<div class="modal fade" id="whatsappNotifyModal" tabindex="-1" aria-labelledby="whatsappNotifyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="whatsappNotifyModalLabel">
                    <i class="fa-brands fa-whatsapp text-success me-2"></i> Notificar via WhatsApp
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="wa-resource-id" value="">
                <input type="hidden" id="wa-resource-type" value="">
                
                <p class="fs-7 text-secondary">Selecione o contato do fornecedor <strong>{{ $contract->provider->name }}</strong> para enviar a notificação:</p>
                
                <div class="list-group mb-3" id="wa-contacts-list">
                    @if($contract->provider->contacts->isEmpty())
                        <div class="text-center p-3 text-muted">
                            <i class="fa-solid fa-users fa-2x mb-2"></i>
                            <p class="mb-0 fs-7">Nenhum contato cadastrado para este fornecedor.</p>
                            <p class="fs-8 mb-0">Cadastre contatos em <a href="{{ route('providers.index') }}">Gerenciar Fornecedores</a>.</p>
                        </div>
                    @else
                        @foreach($contract->provider->contacts as $contact)
                            <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center btn-select-wa-contact" data-phone="{{ preg_replace('/\D/', '', $contact->phone) }}">
                                <div>
                                    <strong>{{ $contact->name }}</strong>
                                    @if($contact->is_main)
                                        <span class="badge bg-success ms-1">Principal</span>
                                    @endif
                                    <div class="text-muted fs-8">{{ $contact->phone ?? 'Sem telefone' }} | {{ $contact->email ?? 'Sem e-mail' }}</div>
                                </div>
                                <i class="fa-brands fa-whatsapp text-success fs-5"></i>
                            </button>
                        @endforeach
                    @endif
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>
@endif

