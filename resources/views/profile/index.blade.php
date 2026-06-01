@extends('layouts.app')

@section('page-title', 'Meu Perfil')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Perfil</li>
@endsection

@section('content')
    <div class="row">
        <!-- Coluna de Informações e Alteração Cadastral -->
        <div class="col-md-6 col-12">
            <div class="card shadow-sm mb-4">
                <div class="card-header border-0 bg-white py-3">
                    <h5 class="mb-0 text-secondary">
                        <i class="fa-solid fa-user-gear me-2 text-primary"></i>
                        Informações Cadastrais
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <!-- Foto de Perfil Atual e Upload -->
                        <div class="d-flex align-items-center gap-3 mb-4">
                            <img src="{{ $user->profile_photo_path ? asset('storage/' . $user->profile_photo_path) : 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($user->email))) . '?d=mp&s=100' }}" 
                                 class="rounded-circle img-thumbnail shadow-sm" 
                                 alt="Foto de Perfil" 
                                 style="width: 80px; height: 80px; object-fit: cover;">
                            <div>
                                <label for="profile_photo" class="form-label fw-bold mb-1">Foto de Perfil:</label>
                                <input type="file" name="profile_photo" id="profile_photo" class="form-control form-control-sm @error('profile_photo') is-invalid @enderror" accept="image/*">
                                @error('profile_photo')
                                    <span class="invalid-feedback d-block" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                                <span class="text-muted fs-8">Arquivos aceitos: JPG, PNG. Tamanho máx. 2MB.</span>
                            </div>
                        </div>

                        <!-- Nome -->
                        <div class="mb-3">
                            <label for="name" class="form-label fw-bold">Nome:</label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                            @error('name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <!-- E-mail -->
                        <div class="mb-3">
                            <label for="email" class="form-label fw-bold">E-mail:</label>
                            <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                            @error('email')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <!-- Nível de Acesso (Apenas leitura) -->
                        <div class="mb-3">
                            <label class="form-label fw-bold text-muted">Nível de Acesso (Perfil):</label>
                            <input type="text" class="form-control-plaintext bg-light px-3 py-2 border rounded" readonly value="@if($user->isSuperAdmin()) Administrador Global @elseif($user->isGestor()) Gestor de Contratos @else Fornecedor @endif">
                        </div>

                        <!-- Vínculo Corporativo (Apenas leitura) -->
                        @if($user->isGestor() && $user->company)
                            <div class="mb-4">
                                <label class="form-label fw-bold text-muted">Empresa Vinculada:</label>
                                <input type="text" class="form-control-plaintext bg-light px-3 py-2 border rounded" readonly value="{{ $user->company->name }}">
                            </div>
                        @elseif($user->isFornecedor() && $user->provider)
                            <div class="mb-4">
                                <label class="form-label fw-bold text-muted">Fornecedor Vinculado:</label>
                                <input type="text" class="form-control-plaintext bg-light px-3 py-2 border rounded" readonly value="{{ $user->provider->name }}">
                            </div>
                        @endif

                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-floppy-disk me-1"></i> Salvar Alterações
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Coluna de Alteração de Senha -->
        <div class="col-md-6 col-12">
            <div class="card shadow-sm mb-4 border-warning-subtle">
                <div class="card-header border-0 bg-white py-3">
                    <h5 class="mb-0 text-secondary">
                        <i class="fa-solid fa-key me-2 text-warning"></i>
                        Alterar Senha de Acesso
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('profile.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <!-- Manter nome/email para satisfazer a validacao se nao enviados -->
                        <input type="hidden" name="name" value="{{ $user->name }}">
                        <input type="hidden" name="email" value="{{ $user->email }}">

                        <p class="text-muted fs-7 mb-3">Caso deseje alterar sua senha, preencha os campos abaixo. Deixe em branco para manter a senha atual.</p>

                        <!-- Nova Senha -->
                        <div class="mb-3">
                            <label for="password" class="form-label fw-bold">Nova Senha:</label>
                            <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" placeholder="Mínimo de 8 caracteres">
                            @error('password')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <!-- Confirmar Nova Senha -->
                        <div class="mb-4">
                            <label for="password_confirmation" class="form-label fw-bold">Confirmar Nova Senha:</label>
                            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" placeholder="Repita a nova senha">
                        </div>

                        <button type="submit" class="btn btn-warning text-dark fw-bold">
                            <i class="fa-solid fa-lock me-1"></i> Atualizar Senha
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
