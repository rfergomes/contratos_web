@extends('layouts.app')

@section('page-title', 'Análise de Documentos (GED)')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">GED - Análise</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm mb-4">
                <div class="card-header border-0 bg-white py-3">
                    <h5 class="mb-0 text-secondary">
                        <i class="fa-solid fa-hourglass-half me-2 text-warning"></i>
                        Aprovações Pendentes
                    </h5>
                    <p class="text-muted mb-0 fs-7">Analise e aprove ou recuse os documentos submetidos pelos fornecedores parceiros.</p>
                </div>
                <div class="card-body p-0">
                    @if($documents->isEmpty())
                        <div class="text-center p-5">
                            <i class="fa-solid fa-circle-check fa-3x text-success mb-3"></i>
                            <p class="text-muted mb-0 fw-bold">Tudo em dia!</p>
                            <p class="text-muted mb-0 fs-7">Não há documentos aguardando análise no momento.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Fornecedor</th>
                                        <th>Contrato</th>
                                        <th>Tipo de Documento</th>
                                        <th>Data de Envio</th>
                                        <th>Arquivo</th>
                                        <th class="text-end px-4" style="min-width: 240px;">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($documents as $doc)
                                        <tr>
                                            <td>
                                                <div class="fw-bold">{{ $doc->contract->provider->name }}</div>
                                                <span class="text-muted fs-7">CNPJ: {{ $doc->contract->provider->cnpj }}</span>
                                            </td>
                                            <td>
                                                <div class="fw-bold">{{ $doc->contract->contract_number }}</div>
                                                <span class="text-muted fs-7">{{ $doc->contract->title }}</span>
                                            </td>
                                            <td>
                                                <div class="fw-bold">{{ $doc->documentType->name }}</div>
                                                <span class="text-muted fs-7">Prazo: {{ $doc->due_date->format('d/m/Y') }}</span>
                                            </td>
                                            <td>
                                                <span>{{ $doc->submitted_at->format('d/m/Y') }}</span>
                                                <small class="d-block text-muted fs-8">{{ $doc->submitted_at->format('H:i') }}</small>
                                            </td>
                                            <td>
                                                <a href="{{ route('ged.download', $doc) }}" class="btn btn-sm btn-outline-danger" title="Visualizar Documento">
                                                    <i class="fa-solid fa-file-pdf me-1"></i> PDF
                                                </a>
                                            </td>
                                            <td class="text-end px-4">
                                                <div class="d-flex justify-content-end gap-2">
                                                    <!-- Botão de Aprovação -->
                                                    <form action="{{ route('ged.approve', $doc) }}" method="POST" class="d-inline form-approve">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-success">
                                                            <i class="fa-solid fa-check me-1"></i> Aprovar
                                                        </button>
                                                    </form>

                                                    <!-- Botão de Recusa (Abre Modal) -->
                                                    <button type="button" class="btn btn-sm btn-danger btn-open-reject" 
                                                            data-id="{{ $doc->id }}" 
                                                            data-url="{{ route('ged.reject', $doc) }}"
                                                            data-provider="{{ $doc->contract->provider->name }}"
                                                            data-document="{{ $doc->documentType->name }}">
                                                        <i class="fa-solid fa-xmark me-1"></i> Recusar
                                                    </button>
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

    <!-- Modal de Recusa de Documento -->
    <div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="form-reject-document" method="POST" action="">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="rejectModalLabel">Recusar Documento</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="fs-7 text-muted mb-3">
                            Você está recusando o documento <strong id="modal-doc-name"></strong> enviado por <strong id="modal-provider-name"></strong>.
                        </p>
                        
                        <div class="mb-3">
                            <label for="rejection_reason" class="form-label fw-bold">Motivo da Recusa / Ajustes Necessários:</label>
                            <textarea name="rejection_reason" id="rejection_reason" rows="4" class="form-control" placeholder="Descreva de forma clara o motivo da rejeição para que o fornecedor saiba o que corrigir (ex: documento vencido, assinatura ausente...)" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger">Confirmar Recusa</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Inicializar Modal do Bootstrap 5
            const rejectModalElement = document.getElementById('rejectModal');
            const rejectModal = new bootstrap.Modal(rejectModalElement);
            const rejectForm = document.getElementById('form-reject-document');
            const modalDocName = document.getElementById('modal-doc-name');
            const modalProviderName = document.getElementById('modal-provider-name');

            // Capturar cliques nos botões de Recusar
            document.querySelectorAll('.btn-open-reject').forEach(button => {
                button.addEventListener('click', function() {
                    const actionUrl = this.getAttribute('data-url');
                    const docName = this.getAttribute('data-document');
                    const providerName = this.getAttribute('data-provider');

                    // Atualizar formulário do modal
                    rejectForm.setAttribute('action', actionUrl);
                    modalDocName.textContent = docName;
                    modalProviderName.textContent = providerName;
                    document.getElementById('rejection_reason').value = '';

                    // Exibir Modal
                    rejectModal.show();
                });
            });

            // Integrar confirmação SweetAlert2 no envio de aprovação
            document.querySelectorAll('.form-approve').forEach(form => {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Confirmar Aprovação?',
                        text: "O documento será marcado em conformidade.",
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
        });
    </script>
@endpush
