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
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Contrato</th>
                                        <th>Tipo de Documento</th>
                                        <th>Periodicidade</th>
                                        <th>Prazo Limite</th>
                                        <th class="text-center">Status</th>
                                        <th>Arquivo Enviado</th>
                                        <th class="text-end px-4">Ação / Upload</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($documents as $doc)
                                        <tr>
                                            <td>
                                                <div class="fw-bold">{{ $doc->contract->contract_number }}</div>
                                                <span class="text-muted fs-7">{{ $doc->contract->title }}</span>
                                            </td>
                                            <td>
                                                <div class="fw-bold">{{ $doc->documentType->name }}</div>
                                                <span class="text-muted fs-7 d-block">{{ $doc->documentType->description }}</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary-subtle text-secondary-emphasis">
                                                    @switch($doc->documentType->periodicity)
                                                        @case('monthly') Mensal @break
                                                        @case('quarterly') Trimestral @break
                                                        @case('semi-annual') Semestral @break
                                                        @case('annual') Anual @break
                                                        @case('once') Único @break
                                                    @endswitch
                                                </span>
                                            </td>
                                            <td>
                                                <span class="fw-bold {{ $doc->due_date->isPast() && $doc->status === 'pending' ? 'text-danger' : 'text-dark' }}">
                                                    {{ $doc->due_date->format('d/m/Y') }}
                                                </span>
                                                @if($doc->due_date->isPast() && $doc->status === 'pending')
                                                    <span class="badge bg-danger ms-1 fs-8">Atrasado</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @switch($doc->status)
                                                    @case('pending')
                                                        <span class="badge bg-danger">Pendente</span>
                                                        @break
                                                    @case('submitted')
                                                        <span class="badge bg-warning text-dark">Aguardando Análise</span>
                                                        @break
                                                    @case('approved')
                                                        <span class="badge bg-success">Aprovado</span>
                                                        @break
                                                    @case('rejected')
                                                        <span class="badge bg-danger">Recusado</span>
                                                        @break
                                                @endswitch
                                            </td>
                                            <td>
                                                @if($doc->file_path)
                                                    <a href="{{ route('ged.download', $doc) }}" class="btn btn-sm btn-link text-decoration-none px-0 fw-bold">
                                                        <i class="fa-solid fa-file-pdf text-danger me-1"></i>
                                                        {{ Str::limit($doc->original_name, 25) }}
                                                    </a>
                                                    <small class="d-block text-muted fs-8">Enviado em {{ $doc->submitted_at->format('d/m/Y H:i') }}</small>
                                                @else
                                                    <span class="text-muted fs-7">—</span>
                                                @endif
                                            </td>
                                            <td class="text-end px-4">
                                                @if($doc->status !== 'approved')
                                                    <!-- Formulário de Upload -->
                                                    <form action="{{ route('ged.upload', $doc) }}" method="POST" enctype="multipart/form-data" class="d-flex justify-content-end align-items-center gap-2">
                                                        @csrf
                                                        <input type="file" name="file" class="form-control form-control-sm" style="max-width: 220px;" required accept=".pdf,image/*">
                                                        <button type="submit" class="btn btn-sm btn-primary">
                                                            <i class="fa-solid fa-cloud-arrow-up me-1"></i> Enviar
                                                        </button>
                                                    </form>
                                                @else
                                                    <span class="text-success"><i class="fa-solid fa-circle-check me-1"></i> Conformidade</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @if($doc->status === 'rejected' && $doc->rejection_reason)
                                            <tr class="table-danger-subtle">
                                                <td colspan="7" class="py-2 px-4 fs-7 text-danger">
                                                    <i class="fa-solid fa-triangle-exclamation me-1"></i>
                                                    <strong>Motivo da Recusa:</strong> "{{ $doc->rejection_reason }}"
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
