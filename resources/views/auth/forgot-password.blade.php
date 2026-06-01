<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Contratos Web') }} - Recuperar Senha</title>
    
    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- FontAwesome para Ícones -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="login-page bg-body-secondary">
    <div class="login-box">
        <div class="card card-outline card-primary shadow">
            <div class="card-header text-center">
                <a href="#" class="h1 text-decoration-none text-dark">
                    <i class="fa-solid fa-file-signature text-primary me-2"></i>
                    Contratos<strong>Web</strong>
                </a>
            </div>
            <div class="card-body login-card-body">
                <p class="login-box-msg text-secondary">Informe seu e-mail para receber o link de recuperação</p>
                
                @if (session('status'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('status') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <form action="{{ route('password.email') }}" method="POST">
                    @csrf
                    
                    <!-- E-mail Input -->
                    <div class="input-group mb-3">
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" placeholder="Seu e-mail cadastrado" value="{{ old('email') }}" required autofocus>
                        <div class="input-group-text">
                            <span class="fa-solid fa-envelope"></span>
                        </div>
                        @error('email')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <!-- Submit -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary w-100">Enviar Link de Recuperação</button>
                        </div>
                    </div>
                </form>

                <p class="mb-1 text-center">
                    <a href="{{ route('login') }}" class="text-decoration-none">Voltar ao login</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
