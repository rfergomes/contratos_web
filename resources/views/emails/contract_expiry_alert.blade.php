<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Alerta de Vencimento de Contrato</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background-color: #f8f9fa;
            color: #333333;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            background-color: #ffffff;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 40px;
            margin: 0 auto;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }
        .header {
            border-bottom: 2px solid #0d6efd;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h2 {
            margin: 0;
            color: #0d6efd;
            font-size: 24px;
        }
        .content p {
            font-size: 16px;
            line-height: 1.6;
            margin: 0 0 16px 0;
        }
        .details-box {
            background-color: #f8f9fa;
            border-left: 4px solid #ffc107;
            padding: 20px;
            margin-bottom: 30px;
            border-radius: 0 4px 4px 0;
        }
        .details-box table {
            width: 100%;
            border-collapse: collapse;
        }
        .details-box table td {
            padding: 6px 0;
            font-size: 15px;
        }
        .details-box table td.label {
            font-weight: bold;
            color: #495057;
            width: 150px;
        }
        .footer {
            margin-top: 30px;
            border-top: 1px solid #dee2e6;
            padding-top: 20px;
            font-size: 13px;
            color: #6c757d;
            text-align: center;
        }
        .btn-action {
            display: inline-block;
            background-color: #0d6efd;
            color: #ffffff !important;
            text-decoration: none;
            padding: 12px 24px;
            font-size: 16px;
            font-weight: bold;
            border-radius: 4px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Alerta de Vencimento de Contrato</h2>
        </div>
        <div class="content">
            <p>Olá, <strong>{{ $contract->responsible->name ?? 'Gestor de Contratos' }}</strong>,</p>
            <p>Este é um alerta automático do sistema para informar que um dos contratos sob sua responsabilidade está próximo do término da vigência.</p>
            
            <div class="details-box">
                <table>
                    <tr>
                        <td class="label">Contrato Nº:</td>
                        <td>{{ $contract->contract_number }}</td>
                    </tr>
                    <tr>
                        <td class="label">Objeto/Título:</td>
                        <td>{{ $contract->title }}</td>
                    </tr>
                    <tr>
                        <td class="label">Cliente:</td>
                        <td>{{ $contract->company->name ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Fornecedor:</td>
                        <td>{{ $contract->provider->name ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Data de Término:</td>
                        <td>{{ $contract->end_date->format('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <td class="label">Dias Restantes:</td>
                        <td><strong style="color: #dc3545;">{{ $contract->alert_days }} dias</strong></td>
                    </tr>
                </table>
            </div>

            <p>É necessário verificar a renovação ou encerramento dos serviços.</p>
            <p style="text-align: center;">
                <a href="{{ url('/contracts') }}" class="btn-action">Acessar Painel de Contratos</a>
            </p>
        </div>
        <div class="footer">
            <p>Este e-mail foi gerado automaticamente pelo módulo CLM da plataforma ContratosWeb.</p>
            <p>&copy; {{ date('Y') }} ContratosWeb. Todos os direitos reservados.</p>
        </div>
    </div>
</body>
</html>
