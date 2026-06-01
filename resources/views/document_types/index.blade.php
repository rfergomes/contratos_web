@extends('layouts.app')

@section('page-title', 'Tipos de Documentações Exigidas (GED)')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Tipos de Documentos</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm mb-4">
                <div class="card-header border-0 bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center w-100">
                        <h5 class="mb-0 text-secondary">
                            <i class="fa-solid fa-folder-open me-2 text-primary"></i>
                            Lista de Tipos de Documentos (GED)
                        </h5>
                        <div class="d-flex align-items-center gap-3">
                            <!-- Switch Tabela / Cards -->
                            <div class="form-check form-switch mb-0 d-flex align-items-center">
                                <input class="form-check-input me-2" type="checkbox" role="switch" id="viewModeSwitch" style="cursor: pointer;">
                                <label class="form-check-label fw-bold text-muted fs-8 mb-0" for="viewModeSwitch" id="viewModeLabel" style="cursor: pointer; user-select: none;">Tabela</label>
                            </div>
                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#createDocTypeModal">
                                <i class="fa-solid fa-plus me-1"></i> Novo Tipo de Documento
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($documentTypes->isEmpty())
                        <div class="text-center p-5">
                            <i class="fa-solid fa-folder-open fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-0">Nenhum tipo de documento cadastrado.</p>
                        </div>
                    @else
                        <!-- MODO TABELA -->
                        <div id="view-table-container" class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 80px;">ID</th>
                                        <th>Nome</th>
                                        <th>Descrição</th>
                                        <th>Periodicidade</th>
                                        <th class="text-center">Obrigatório por Padrão</th>
                                        <th class="text-end px-4" style="width: 200px;">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($documentTypes as $type)
                                        <tr>
                                            <td>{{ $type->id }}</td>
                                            <td><strong>{{ $type->name }}</strong></td>
                                            <td class="text-muted fs-7">{{ $type->description ?? 'Sem descrição' }}</td>
                                            <td>
                                                @switch($type->periodicity)
                                                    @case('monthly') <span class="badge bg-light text-dark border">Mensal</span> @break
                                                    @case('quarterly') <span class="badge bg-light text-dark border">Trimestral</span> @break
                                                    @case('semi-annual') <span class="badge bg-light text-dark border">Semestral</span> @break
                                                    @case('annual') <span class="badge bg-light text-dark border">Anual</span> @break
                                                    @case('once') <span class="badge bg-light text-dark border">Único</span> @break
                                                @endswitch
                                            </td>
                                            <td class="text-center">
                                                <form action="{{ route('document-types.toggle', $type) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <div class="form-check form-switch d-inline-block align-middle">
                                                        <input class="form-check-input" type="checkbox" role="switch" onChange="this.form.submit()" {{ $type->required ? 'checked' : '' }}>
                                                        <label class="form-check-label fw-bold text-secondary fs-7 ms-1">{{ $type->required ? 'Sim' : 'Não' }}</label>
                                                    </div>
                                                </form>
                                            </td>
                                            <td class="text-end px-4">
                                                <div class="d-flex justify-content-end gap-2">
                                                    <!-- Edit Button -->
                                                    <button type="button" class="btn btn-sm btn-primary btn-edit-doctype" 
                                                            data-id="{{ $type->id }}"
                                                            data-name="{{ $type->name }}"
                                                            data-description="{{ $type->description }}"
                                                            data-periodicity="{{ $type->periodicity }}"
                                                            data-required="{{ $type->required ? '1' : '0' }}"
                                                            data-url="{{ route('document-types.update', $type) }}">
                                                        <i class="fa-solid fa-pen-to-square"></i> Editar
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- MODO CARDS -->
                        <div id="view-card-container" class="p-4 d-none">
                            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-3">
                                @foreach($documentTypes as $type)
                                    <div class="col">
                                        <div class="card h-100 shadow-sm border border-light">
                                            <div class="card-body">
                                                <div class="d-flex align-items-center mb-3">
                                                    <div class="bg-primary-subtle text-primary rounded p-2 me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                        <i class="fa-solid fa-folder-open fs-5"></i>
                                                    </div>
                                                    <div>
                                                        <h6 class="fw-bold mb-0 text-dark">{{ $type->name }}</h6>
                                                        <small class="text-muted">ID: {{ $type->id }}</small>
                                                    </div>
                                                </div>
                                                <p class="mb-2 fs-7 text-secondary" style="min-height: 40px;">
                                                    {{ Str::limit($type->description ?? 'Sem descrição', 100) }}
                                                </p>
                                                <p class="mb-3 fs-7">
                                                    <strong>Periodicidade:</strong>
                                                    @switch($type->periodicity)
                                                        @case('monthly') <span class="badge bg-light text-dark border">Mensal</span> @break
                                                        @case('quarterly') <span class="badge bg-light text-dark border">Trimestral</span> @break
                                                        @case('semi-annual') <span class="badge bg-light text-dark border">Semestral</span> @break
                                                        @case('annual') <span class="badge bg-light text-dark border">Anual</span> @break
                                                        @case('once') <span class="badge bg-light text-dark border">Único</span> @break
                                                    @endswitch
                                                </p>
                                                <div class="d-flex justify-content-between align-items-center border-top pt-3">
                                                    <form action="{{ route('document-types.toggle', $type) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('PATCH')
                                                        <div class="form-check form-switch align-middle mb-0">
                                                            <input class="form-check-input" type="checkbox" role="switch" onChange="this.form.submit()" {{ $type->required ? 'checked' : '' }}>
                                                            <label class="form-check-label fw-bold text-secondary fs-8 ms-1">Obrigatório</label>
                                                        </div>
                                                    </form>
                                                    <button type="button" class="btn btn-xs btn-primary btn-edit-doctype" 
                                                            data-id="{{ $type->id }}"
                                                            data-name="{{ $type->name }}"
                                                            data-description="{{ $type->description }}"
                                                            data-periodicity="{{ $type->periodicity }}"
                                                            data-required="{{ $type->required ? '1' : '0' }}"
                                                            data-url="{{ route('document-types.update', $type) }}">
                                                        <i class="fa-solid fa-pen-to-square me-1"></i> Editar
                                                    </button>
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

    <!-- MODAL DE CADASTRO -->
    <div class="modal fade" id="createDocTypeModal" tabindex="-1" aria-labelledby="createDocTypeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('document-types.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="createDocTypeModalLabel">Novo Tipo de Documento</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="create_name" class="form-label fw-bold">Nome do Documento:</label>
                            <input type="text" name="name" id="create_name" class="form-control @if($errors->any() && !old('_method')) is-invalid @endif" value="{{ old('name') }}" placeholder="Ex: Certidão de FGTS" required>
                            @if($errors->any() && !old('_method'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('name') }}</strong>
                                </span>
                            @endif
                        </div>

                        <div class="mb-3">
                            <label for="create_description" class="form-label fw-bold">Descrição / Instruções:</label>
                            <textarea name="description" id="create_description" rows="3" class="form-control" placeholder="Instruções para o fornecedor sobre o documento...">{{ old('description') }}</textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 col-12 mb-3">
                                <label for="create_periodicity" class="form-label fw-bold">Periodicidade de Envio:</label>
                                <select name="periodicity" id="create_periodicity" class="form-select" required>
                                    <option value="monthly" {{ old('periodicity') == 'monthly' ? 'selected' : '' }}>Mensal</option>
                                    <option value="quarterly" {{ old('periodicity') == 'quarterly' ? 'selected' : '' }}>Trimestral</option>
                                    <option value="semi-annual" {{ old('periodicity') == 'semi-annual' ? 'selected' : '' }}>Semestral</option>
                                    <option value="annual" {{ old('periodicity') == 'annual' ? 'selected' : '' }}>Anual</option>
                                    <option value="once" {{ old('periodicity') == 'once' ? 'selected' : '' }}>Único / Uma vez</option>
                                </select>
                            </div>
                            <div class="col-md-6 col-12 mb-3 d-flex align-items-end">
                                <div class="form-check mb-2">
                                    <input type="checkbox" name="required" id="create_required" class="form-check-input" value="1" {{ old('required') ? 'checked' : '' }}>
                                    <label for="create_required" class="form-check-label fw-bold">Obrigatório por Padrão</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save me-1"></i> Salvar Documento</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL DE EDIÇÃO -->
    <div class="modal fade" id="editDocTypeModal" tabindex="-1" aria-labelledby="editDocTypeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="form-edit-doctype" action="" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <input type="hidden" name="doctype_id" id="edit_doctype_id" value="{{ old('doctype_id') }}">

                    <div class="modal-header">
                        <h5 class="modal-title" id="editDocTypeModalLabel">Editar Tipo de Documento</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit_name" class="form-label fw-bold">Nome do Documento:</label>
                            <input type="text" name="name" id="edit_name" class="form-control @if($errors->any() && old('_method') === 'PUT') is-invalid @endif" value="{{ old('name') }}" required>
                            @if($errors->any() && old('_method') === 'PUT')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('name') }}</strong>
                                </span>
                            @endif
                        </div>

                        <div class="mb-3">
                            <label for="edit_description" class="form-label fw-bold">Descrição / Instruções:</label>
                            <textarea name="description" id="edit_description" rows="3" class="form-control">{{ old('description') }}</textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 col-12 mb-3">
                                <label for="edit_periodicity" class="form-label fw-bold">Periodicidade de Envio:</label>
                                <select name="periodicity" id="edit_periodicity" class="form-select" required>
                                    <option value="monthly">Mensal</option>
                                    <option value="quarterly">Trimestral</option>
                                    <option value="semi-annual">Semestral</option>
                                    <option value="annual">Anual</option>
                                    <option value="once">Único / Uma vez</option>
                                </select>
                            </div>
                            <div class="col-md-6 col-12 mb-3 d-flex align-items-end">
                                <div class="form-check mb-2">
                                    <input type="checkbox" name="required" id="edit_required" class="form-check-input" value="1">
                                    <label for="edit_required" class="form-check-label fw-bold">Obrigatório por Padrão</label>
                                </div>
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
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const createModal = new bootstrap.Modal(document.getElementById('createDocTypeModal'));
            const editModal = new bootstrap.Modal(document.getElementById('editDocTypeModal'));
            
            const editForm = document.getElementById('form-edit-doctype');
            const editIdInput = document.getElementById('edit_doctype_id');
            const editNameInput = document.getElementById('edit_name');
            const editDescriptionInput = document.getElementById('edit_description');
            const editPeriodicityInput = document.getElementById('edit_periodicity');
            const editRequiredInput = document.getElementById('edit_required');

            // Capturar clique no botão editar
            document.querySelectorAll('.btn-edit-doctype').forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const name = this.getAttribute('data-name');
                    const description = this.getAttribute('data-description');
                    const periodicity = this.getAttribute('data-periodicity');
                    const required = this.getAttribute('data-required') === '1';
                    const actionUrl = this.getAttribute('data-url');

                    // Preencher campos
                    editForm.setAttribute('action', actionUrl);
                    editIdInput.value = id;
                    editNameInput.value = name;
                    editDescriptionInput.value = description !== 'null' && description !== null ? description : '';
                    editPeriodicityInput.value = periodicity;
                    editRequiredInput.checked = required;

                    // Mostrar modal
                    editModal.show();
                });
            });

            // Reabrir modal caso haja erros de validação
            @if($errors->any())
                @if(old('_method') === 'PUT')
                    const oldId = "{{ old('doctype_id') }}";
                    if (oldId) {
                        editForm.setAttribute('action', "/document-types/" + oldId);
                        editModal.show();
                    }
                @else
                    createModal.show();
                @endif
            @endif

            // View Mode Toggle
            const toggleSwitch = document.getElementById('viewModeSwitch');
            const tableContainer = document.getElementById('view-table-container');
            const cardContainer = document.getElementById('view-card-container');
            const modeLabel = document.getElementById('viewModeLabel');
            
            if (toggleSwitch && tableContainer && cardContainer) {
                const savedMode = localStorage.getItem('view_mode_document_types') || 'table';
                
                const setMode = (mode) => {
                    if (mode === 'card') {
                        tableContainer.classList.add('d-none');
                        cardContainer.classList.remove('d-none');
                        toggleSwitch.checked = true;
                        if (modeLabel) modeLabel.textContent = 'Cards';
                    } else {
                        tableContainer.classList.remove('d-none');
                        cardContainer.classList.add('d-none');
                        toggleSwitch.checked = false;
                        if (modeLabel) modeLabel.textContent = 'Tabela';
                    }
                    localStorage.setItem('view_mode_document_types', mode);
                };
                
                setMode(savedMode);
                
                toggleSwitch.addEventListener('change', function() {
                    setMode(this.checked ? 'card' : 'table');
                });
            }
        });
    </script>
@endpush
