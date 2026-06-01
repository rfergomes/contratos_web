<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Contratos Web') }} - Acesso Seguro</title>
    
    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- FontAwesome para Ícones -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body.magic-login-body {
            background: linear-gradient(135deg, #0b0f19 0%, #111827 50%, #1e1b4b 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Outfit', sans-serif;
            color: #f3f4f6;
            margin: 0;
            padding: 20px;
            overflow-x: hidden;
            position: relative;
        }

        /* Ambient light effects */
        .ambient-glow-1 {
            position: absolute;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.15) 0%, rgba(99, 102, 241, 0) 70%);
            top: -100px;
            left: -100px;
            z-index: 0;
            pointer-events: none;
        }
        .ambient-glow-2 {
            position: absolute;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(16, 185, 129, 0.12) 0%, rgba(16, 185, 129, 0) 70%);
            bottom: -100px;
            right: -100px;
            z-index: 0;
            pointer-events: none;
        }

        .magic-card {
            background: rgba(17, 24, 39, 0.7);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 24px;
            padding: 40px;
            width: 100%;
            max-width: 480px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            z-index: 10;
            position: relative;
            animation: fadeInUp 0.8s cubic-bezier(0.16, 1, 0.3, 1);
            text-align: center;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .brand-header {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-bottom: 24px;
        }

        .brand-icon-wrapper {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 20px rgba(16, 185, 129, 0.3);
        }

        .brand-icon-wrapper i {
            font-size: 22px;
            color: #ffffff;
        }

        .brand-text {
            font-size: 26px;
            font-weight: 700;
            letter-spacing: -0.5px;
            background: linear-gradient(to right, #ffffff, #e2e8f0);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .brand-text strong {
            font-weight: 800;
            background: linear-gradient(to right, #10b981, #34d399);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .security-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.2);
            color: #34d399;
            padding: 6px 14px;
            border-radius: 50px;
            font-size: 13px;
            font-weight: 500;
            margin-bottom: 28px;
        }

        .welcome-title {
            font-size: 20px;
            font-weight: 600;
            color: #ffffff;
            margin-bottom: 8px;
        }

        .welcome-subtitle {
            font-size: 14px;
            color: #9ca3af;
            margin-bottom: 32px;
            line-height: 1.5;
        }

        .details-panel {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 32px;
            text-align: left;
        }

        .detail-item {
            margin-bottom: 16px;
        }

        .detail-item:last-child {
            margin-bottom: 0;
        }

        .detail-label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #6b7280;
            margin-bottom: 4px;
            font-weight: 600;
        }

        .detail-value {
            font-size: 14px;
            color: #e5e7eb;
            font-weight: 500;
        }

        .detail-value strong {
            color: #ffffff;
            font-weight: 600;
        }

        .btn-access {
            width: 100%;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border: none;
            border-radius: 14px;
            color: #ffffff;
            padding: 15px 24px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 10px 25px -5px rgba(16, 185, 129, 0.4);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-access:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 30px -5px rgba(16, 185, 129, 0.5);
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
        }

        .btn-access:active {
            transform: translateY(1px);
        }

        .footer-note {
            margin-top: 24px;
            font-size: 12px;
            color: #6b7280;
        }

        /* Loading spinner styling */
        .spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: #ffffff;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .submitting .spinner {
            display: inline-block;
        }

        .submitting .btn-text {
            opacity: 0.8;
        }
    </style>
</head>
<body class="magic-login-body">
    <div class="ambient-glow-1"></div>
    <div class="ambient-glow-2"></div>

    <div class="magic-card">
        <!-- Logo -->
        <div class="brand-header">
            <div class="brand-icon-wrapper">
                <i class="fa-solid fa-file-signature"></i>
            </div>
            <div class="brand-text">
                Contratos<strong>Web</strong>
            </div>
        </div>

        <!-- Security Badge -->
        <div>
            <span class="security-badge">
                <i class="fa-solid fa-shield-halved"></i> Conexão Segura Ativa
            </span>
        </div>

        <!-- Welcome Text -->
        <h1 class="welcome-title">Verificação de Acesso</h1>
        <p class="welcome-subtitle">
            Você está prestes a acessar com segurança as demandas ativas do seu contrato sem necessidade de digitar login e senha.
        </p>

        <!-- Details Panel -->
        <div class="details-panel">
            <div class="detail-item">
                <div class="detail-label">Fornecedor</div>
                <div class="detail-value">{{ $provider->name }}</div>
            </div>
            
            <div class="detail-item">
                <div class="detail-label">Contrato</div>
                <div class="detail-value">
                    <strong>{{ $contract->contract_number }}</strong> - {{ $contract->title }}
                </div>
            </div>

            @if($resourceName)
                <div class="detail-item">
                    <div class="detail-label">Recurso</div>
                    <div class="detail-value text-info">
                        <i class="fa-solid fa-circle-info me-1"></i> {{ $resourceName }}
                    </div>
                </div>
            @endif
        </div>

        <!-- Form & Button -->
        <form action="{{ route('public.access.authenticate', $token) }}" method="POST" id="magic-login-form">
            @csrf
            <button type="submit" class="btn-access" id="submit-btn">
                <span class="btn-text">Confirmar e Acessar</span>
                <div class="spinner" id="spinner"></div>
            </button>
        </form>

        <p class="footer-note">
            <i class="fa-regular fa-clock me-1"></i> Este link temporário expira em breve e é de uso único.
        </p>
    </div>

    <script>
        document.getElementById('magic-login-form').addEventListener('submit', function() {
            var btn = document.getElementById('submit-btn');
            var spinner = document.getElementById('spinner');
            btn.classList.add('submitting');
            btn.disabled = true;
            spinner.style.display = 'inline-block';
        });
    </script>
</body>
</html>
