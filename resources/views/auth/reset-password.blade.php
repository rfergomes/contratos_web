<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Contratos Web') }} - Redefinir Senha</title>
    
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
                <p class="login-box-msg text-secondary">Preencha os campos para redefinir sua senha</p>

                <form action="{{ route('password.update') }}" method="POST">
                    @csrf
                    
                    <input type="hidden" name="token" value="{{ $token }}">

                    <!-- E-mail Input -->
                    <div class="input-group mb-3">
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" placeholder="E-mail" value="{{ $email ?? old('email') }}" required autofocus readonly>
                        <div class="input-group-text">
                            <span class="fa-solid fa-envelope"></span>
                        </div>
                        @error('email')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <!-- Nova Senha Input -->
                    <div class="input-group mb-3">
                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="Nova Senha" required>
                        <div class="input-group-text">
                            <span class="fa-solid fa-lock"></span>
                        </div>
                        @error('password')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <!-- Confirmar Senha Input -->
                    <div class="input-group mb-3">
                        <input type="password" name="password_confirmation" class="form-control" placeholder="Confirmar Nova Senha" required>
                        <div class="input-group-text">
                            <span class="fa-solid fa-lock"></span>
                        </div>
                    </div>

                    <!-- Submit -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary w-100">Redefinir Senha</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
