@extends('layouts.app')

@section('page-title', 'Obrigações Documentais (GED)')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">GED - Fornecedor</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm mb-4">
                <div class="card-header border-0 bg-white py-3">
                    <h5 class="mb-0 text-secondary">
                        <i class="fa-solid fa-list-check me-2 text-primary"></i>
                        Minhas Pendências Documentais
                    </h5>
                    <p class="text-muted mb-0 fs-7">Faça o upload dos documentos obrigatórios dentro dos prazos estabelecidos.</p>
                </div>
                <div class="card-body p-0">
                    @if($documents->isEmpty())
                        <div class="text-center p-5">
                            <i class="fa-regular fa-folder-open fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-0">Nenhuma obrigação documental pendente vinculada aos seus contratos.</p>
                        </div>
                    @else
                        {{-- MODO CARDS (mobile-first, padrão em todos os tamanhos) --}}
                        <div class="d-grid gap-0">
                            @foreach($documents as $doc)
                                @php
                                    $isOverdue = $doc->due_date->isPast() && $doc->status === 'pending';
                                    $sideColor = match($doc->status) {
                                        'approved'  => '#28a745',
                                        'rejected'  => '#dc3545',
                                        'submitted' => '#ffc107',
                                        default     => $isOverdue ? '#dc3545' : '#dee2e6',
                                    };
                                @endphp
                                <div class="border-bottom px-3 py-3" style="border-left: 4px solid {{ $sideColor }} !important;">
                                    {{-- Linha superior: Contrato + Status --}}
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <span class="badge bg-primary-subtle text-primary-emphasis fw-bold">
                                                {{ $doc->contract->contract_number }}
                                            </span>
                                            <span class="text-muted fs-8 ms-1">{{ Str::limit($doc->contract->title, 30) }}</span>
                                        </div>
                                        <div>
                                            @switch($doc->status)
                                                @case('pending')
                                                    <span class="badge bg-danger">Pendente</span>
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
                                        </div>
                                    </div>

                                    {{-- Nome e descrição do documento --}}
                                    <div class="fw-semibold text-dark mb-1">{{ $doc->documentType->name }}</div>
                                    @if($doc->documentType->description)
                                        <div class="text-muted fs-8 mb-2">{{ $doc->documentType->description }}</div>
                                    @endif

                                    {{-- Periodicidade + Prazo --}}
                                    <div class="d-flex gap-2 flex-wrap mb-2">
                                        <span class="badge bg-secondary-subtle text-secondary-emphasis">
                                            @switch($doc->documentType->periodicity)
                                                @case('monthly') Mensal @break
                                                @case('quarterly') Trimestral @break
                                                @case('semi-annual') Semestral @break
                                                @case('annual') Anual @break
                                                @case('once') Único @break
                                            @endswitch
                                        </span>
                                        <span class="badge {{ $isOverdue ? 'bg-danger' : 'bg-light text-dark border' }}">
                                            <i class="fa-regular fa-calendar me-1"></i>
                                            Prazo: {{ $doc->due_date->format('d/m/Y') }}
                                            @if($isOverdue) · Atrasado @endif
                                        </span>
                                    </div>

                                    {{-- Arquivo enviado --}}
                                    @if($doc->file_path)
                                        <div class="mb-2">
                                            <a href="{{ route('ged.download', $doc) }}" class="btn btn-sm btn-link text-decoration-none px-0 fw-bold">
                                                <i class="fa-solid fa-file-pdf text-danger me-1"></i>
                                                {{ Str::limit($doc->original_name, 30) }}
                                            </a>
                                            <small class="d-block text-muted fs-8">Enviado em {{ $doc->submitted_at->format('d/m/Y H:i') }}</small>
                                        </div>
                                    @endif

                                    {{-- Motivo de recusa --}}
                                    @if($doc->status === 'rejected' && $doc->rejection_reason)
                                        <div class="alert alert-danger py-2 px-3 fs-8 mb-2">
                                            <i class="fa-solid fa-triangle-exclamation me-1"></i>
                                            <strong>Motivo da Recusa:</strong> "{{ $doc->rejection_reason }}"
                                        </div>
                                    @endif

                                    {{-- Ação de upload --}}
                                    @if($doc->status !== 'approved')
                                        <form action="{{ route('ged.upload', $doc) }}" method="POST" enctype="multipart/form-data" class="d-flex align-items-center gap-2 flex-wrap mt-2">
                                            @csrf
                                            <input type="file" name="file" class="form-control form-control-sm flex-grow-1" required accept=".pdf,image/*">
                                            <button type="submit" class="btn btn-sm btn-primary">
                                                <i class="fa-solid fa-cloud-arrow-up me-1"></i> Enviar
                                            </button>
                                        </form>
                                    @else
                                        <div class="text-success fs-7 mt-1">
                                            <i class="fa-solid fa-circle-check me-1"></i> Conformidade Confirmada
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
